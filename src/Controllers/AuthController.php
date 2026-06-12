<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\Repositories\UserRepository;

class AuthController extends Controller {
    private const GOOGLE_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_USERINFO_URL = 'https://www.googleapis.com/oauth2/v3/userinfo';

    /**
     * Exibe a tela de login.
     * @return void
     */
    public function index() {
        $this->view('auth/login.twig');
    }

    /**
     * Autentica um usuario usando e-mail e senha.
     * @return void
     */
    public function login() {
        // Normaliza os dados do formulário de login.
        $data = [
            'email' => $_POST['email'] ?? null,
            'password' => $_POST['password'] ?? '',
        ];

        // Busca o usuário pelo e-mail informado.
        $repository = new UserRepository;
        $user = $repository->find([
            'email' => $data['email']
        ]);

        // Encerra o fluxo se o e-mail não existir.
        if (empty($user) || $user == null) {
            $this->failStandardLogin('E-mail ou senha invalidos.');
        }

        // Valida a senha e inicia a sessão.
        if (!password_verify($data['password'], $user['password'])) {
            $message = ($user['auth_provider'] ?? 'local') === 'google'
                ? 'Esta conta usa login com Google.'
                : 'E-mail ou senha invalidos.';

            $this->failStandardLogin($message);
        }

        Session::set('user_id', $user['id']);
        redirect();
        exit;
    }

    /**
     * Verifica se um e-mail já está cadastrado.
     * @return void
     */
    public function checkEmail() {
        // E-mail usado pela validação assíncrona do formulario.
        $email = trim($_POST['email'] ?? '');
        $repository = new UserRepository;
        $user = $email !== '' ? $repository->find([
            'email' => $email
        ]) : null;

        // Resposta em JSON esperada pelo validador do frontend.
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'exists' => !empty($user),
        ]);
    }

    /**
     * Redireciona o usuário para a tela de autorização do Google.
     * @return void
     */
    public function googleRedirect() {
        $this->ensureGoogleConfig();

        // State: token anti-CSRF usado para validar o callback.
        $state = bin2hex(random_bytes(16));
        Session::set('google_oauth_state', $state);

        // Parâmetros exigidos pelo fluxo OAuth.
        $params = [
            'client_id' => $this->getGoogleClientId(),
            'redirect_uri' => $this->getGoogleRedirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ];

        // Direciona o usuário para o Google escolher/autorizar a conta.
        header('Location: ' . self::GOOGLE_AUTH_URL . '?' . http_build_query($params));
        exit;
    }

    /**
     * Recebe o callback do Google e finaliza o login OAuth.
     * @return void
     */
    public function googleCallback() {
        $this->ensureGoogleConfig();

        // Valida o state retornado pelo Google contra o valor salvo na sessão.
        $state = $_GET['state'] ?? '';
        $expectedState = Session::get('google_oauth_state');
        Session::remove('google_oauth_state');

        if (empty($state) || empty($expectedState) || !hash_equals($expectedState, $state)) {
            $this->failGoogleLogin('Estado OAuth invalido.');
        }

        // Se o usuário cancelar no Google, o callback volta com erro.
        if (!empty($_GET['error'])) {
            $this->failGoogleLogin('Login com Google cancelado.');
        }

        // Code: credencial temporária usada para obter o access token.
        $code = $_GET['code'] ?? '';

        if (empty($code)) {
            $this->failGoogleLogin('Codigo OAuth ausente.');
        }

        // Troca o code por token e usa o token para buscar o perfil.
        $token = $this->requestGoogleToken($code);
        $profile = $this->requestGoogleProfile($token['access_token'] ?? '');

        // O e-mail precisa vir verificado pelo Google para ser confiável.
        if (empty($profile['sub']) || empty($profile['email']) || empty($profile['email_verified'])) {
            $this->failGoogleLogin('Nao foi possivel validar o e-mail do Google.');
        }

        // Localiza/cria o usuário e inicia a sessão.
        $user = $this->findOrCreateGoogleUser($profile);
        Session::set('user_id', $user['id']);

        redirect();
        exit;
    }

    /**
     * Encerra a sessão do usuario.
     * @return void
     */
    public function logoff() {
        Session::remove('user_id');
        redirect();
        exit;
    }

    /**
     * Localiza/cria um usuario a partir do perfil do Google.
     * @return array
     */
    private function findOrCreateGoogleUser(array $profile): array {
        $repository = new UserRepository;
        $googleId = $profile['sub'];
        $email = $profile['email'];
        $name = $profile['name'] ?? $email;

        // Primeiro, tenta encontrar um usuário já vinculado a este Google ID.
        $user = $repository->find([
            'google_id' => $googleId
        ]);

        if (!empty($user)) {
            return $user;
        }

        // Se ainda não houver vinculo, tenta encontrar uma conta local pelo e-mail.
        $user = $repository->find([
            'email' => $email
        ]);

        if (!empty($user)) {
            // Evita vincular o mesmo e-mail a duas contas Google diferentes.
            if (!empty($user['google_id']) && $user['google_id'] !== $googleId) {
                $this->failGoogleLogin('Este e-mail já esta vinculado a outra conta Google.');
            }

            // Vincula a conta local existente ao Google.
            $data = array_merge($user, [
                'google_id' => $googleId,
            ]);

            $repository->save($data);

            return $repository->find([
                'id' => $user['id']
            ]);
        }

        // Se não existe conta alguma, cria um novo usuário via Google.
        $repository->save([
            'id' => null,
            'name' => $name,
            'email' => $email,
            'password' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
            'google_id' => $googleId,
            'auth_provider' => 'google',
        ]);

        // Retorna o usuário recem-criado já com o ID gerado pelo banco.
        return $repository->find([
            'google_id' => $googleId
        ]);
    }

    /**
     * Troca o code OAuth por um access token do Google.
     * @return array
     */
    private function requestGoogleToken(string $code): array {
        // O endpoint de token recebe dados como form-urlencoded.
        $response = $this->post(self::GOOGLE_TOKEN_URL, [
            'code' => $code,
            'client_id' => $this->getGoogleClientId(),
            'client_secret' => $this->getGoogleClientSecret(),
            'redirect_uri' => $this->getGoogleRedirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        // Sem access_token, não há como consultar o perfil do usuario.
        if (empty($response['access_token'])) {
            $this->failGoogleLogin('Não foi possivel obter o token do Google.');
        }

        return $response;
    }

    /**
     * Busca os dados públicos do usuário autenticado no Google.
     * @return array
     */
    private function requestGoogleProfile(string $accessToken): array {
        if (empty($accessToken)) {
            $this->failGoogleLogin('Token do Google ausente.');
        }

        // O token vai no header Authorization como Bearer token.
        return $this->get(self::GOOGLE_USERINFO_URL, [
            'Authorization: Bearer ' . $accessToken,
        ]);
    }

    /**
     * Executa uma requisição POST e decodifica a resposta JSON.
     * @return array
     */
    private function post(string $url, array $data): array {
        // Usa stream_context para evitar dependência externa de cliente HTTP.
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
                'content' => http_build_query($data),
                'ignore_errors' => true,
            ],
        ]);

        return $this->decodeJsonResponse(@file_get_contents($url, false, $context));
    }

    /**
     * Executa uma requisição GET e decodifica a resposta JSON.
     * @return array
     */
    private function get(string $url, array $headers = []): array {
        // Junta headers padrao com headers específicos da chamada.
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", array_merge(['Accept: application/json'], $headers)) . "\r\n",
                'ignore_errors' => true,
            ],
        ]);

        return $this->decodeJsonResponse(@file_get_contents($url, false, $context));
    }

    /**
     * Converte a resposta JSON em array e centraliza falhas do Google.
     * @return array
     */
    private function decodeJsonResponse($response): array {
        // file_get_contents retorna false em erro de rede/SSL/DNS.
        if ($response === false) {
            $this->failGoogleLogin('Falha de comunicação com o Google.');
        }

        // Decodifica a resposta de sucesso ou erro enviada pelo Google.
        $data = json_decode($response, true);

        if (!is_array($data)) {
            $this->failGoogleLogin('Resposta inválida do Google.');
        }

        return $data;
    }

    /**
     * Obtém o Client ID configurado no .env.
     * @return string
     */
    private function getGoogleClientId(): string {
        return $_ENV['GOOGLE_CLIENT_ID'] ?? '';
    }

    /**
     * Obtém o Client Secret configurado no .env.
     * @return string
     */
    private function getGoogleClientSecret(): string {
        return $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
    }

    /**
     * Obtém a URL de callback configurada no .env.
     * @return string
     */
    private function getGoogleRedirectUri(): string {
        return $_ENV['GOOGLE_REDIRECT_URI'] ?? '';
    }

    /**
     * Garante que as credenciais do Google estejam configuradas.
     * @return void
     */
    private function ensureGoogleConfig(): void {
        if (empty($this->getGoogleClientId()) || empty($this->getGoogleClientSecret()) || empty($this->getGoogleRedirectUri())) {
            $this->failGoogleLogin('Configuracao do Google OAuth incompleta.');
        }
    }

    /**
     * Encerra o fluxo OAuth exibindo uma mensagem simples de erro.
     * @return void
     */
    private function failGoogleLogin(string $message): void {
        echo $message;
        exit;
    }

    /**
     * Reexibe o formulario com uma mensagem de falha no login padrao.
     * @return void
     */
    private function failStandardLogin(string $message): void {
        $this->view('auth/login.twig', [
            'login_error' => $message
        ]);
        exit;
    }
}
