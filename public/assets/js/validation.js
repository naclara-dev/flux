(function () {
    function validateForms(forms) {
        let formIsValid = true;

        getElements(forms).forEach(function (form) {
            if (!validateForm(form)) {
                formIsValid = false;
            }
        });

        return formIsValid;
    }

    function validateForm(form) {
        cleanMessages(form);
        let isValid = true;

        form.querySelectorAll('input[data-type], select[data-type]').forEach(function (input) {
            const types = getTypes(input.dataset.type);

            for (let i = 0; i < types.length; i++) {
                const type = types[i];
                const result = validateByType(type, input);

                if (!result) {
                    isValid = false;
                    const msg = getErrorText(type, input);

                    showErrorMessage(input, msg);
                    break;
                }
            }
        });

        return isValid;
    }

    const validators = {
        required: {
            validate: validateRequired,
            message: "ops! campo obrigatório."
        },
        date: {
            validate: validateDate,
            message: "data inválida. :/"
        },
        email: {
            validate: validateEmail,
            message: "e-mail inválido :/"
        },
        unique: {
            validate: validateUnique,
            message: "ops! valor já cadastrado.",
            types: {
                email: {
                    message: "ops! e-mail já cadastrado."
                }
            }
        },
        exists: {
            validate: validateExists,
            message: "ops! parece que você não tem uma conta :/",
            types: {
                email: {
                    message: "ops! parece que você não tem uma conta :/",    
                }
            }
        }
    };

    function validateByType(type, input) {
        const rule = validators[type];

        if (rule) {
            return rule.validate(input);
        }

        return true;
    }

    function getTypes(type) {
        return String(type).split('|').map(function (item) {
            return item.trim();
        }).filter(function (item) {
            return item !== '';
        });
    }

    function validateEmail(input) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(input.value);
    }

    function validateRequired(input) {
        return input.value !== '' && input.value !== null;
    }

    function validateDate(input) {
        const regex = /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/;
        const value = input.value;
        let compare = true;

        if (input.dataset.compare !== undefined) {
            const compareInput = document.querySelector(input.dataset.compare);
            const compareValue = compareInput ? compareInput.value : '';

            compare = getDaysBetween(value, compareValue) >= 0;
        }

        return regex.test(value) && isCompleteDate(value) && compare;
    }

    function validateExists(input) {
        //
    }

    function validateUnique(input) {
        const url = input.dataset.uniqueUrl;
        const name = input.getAttribute('name');
        let isUnique = true;
        let data = {};

        if (!url || !name) {
            return true;
        }

        data[name] = input.value;
        data = Object.assign(data, getUniqueExtraData(input));

        const request = new XMLHttpRequest();
        request.open('POST', url, false);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.setRequestHeader('Accept', 'application/json');

        try {
            request.send(new URLSearchParams(data).toString());

            if (request.status >= 200 && request.status < 300) {
                const response = JSON.parse(request.responseText);
                isUnique = response.available === true;
            } else {
                isUnique = false;
            }
        } catch (error) {
            isUnique = false;
        }

        return isUnique;
    }    

    function getDaysBetween(startDate, endDate, allowZero) {
        if (typeof window.getDaysBetween === 'function') {
            return window.getDaysBetween(startDate, endDate, allowZero);
        }

        const start = parseDataBR(startDate);
        const end = parseDataBR(endDate);

        if (!end || !start) {
            return NaN;
        }

        start.setHours(0, 0, 0, 0);
        end.setHours(0, 0, 0, 0);

        const diff = (end - start) / 86400000;

        return diff === 0 && allowZero === false ? 1 : diff;
    }

    function parseDataBR(value) {
        if (typeof window.parseDataBR === 'function') {
            return window.parseDataBR(value);
        }

        const parts = String(value).split('/');

        if (parts.length !== 3) {
            return null;
        }

        const day = Number(parts[0]);
        const month = Number(parts[1]);
        const year = Number(parts[2]);
        const date = new Date(year, month - 1, day);

        if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
            return null;
        }

        return date;
    }

    function isCompleteDate(value) {
        if (typeof window.isCompleteDate === 'function') {
            return window.isCompleteDate(value);
        }

        return /^\d{2}\/\d{2}\/\d{4}$/.test(value);
    }

    function getUniqueExtraData(input) {
        const form = input.closest('form');
        const fields = String(input.dataset.uniqueExtraFields || '').split(',');
        let data = {};

        if (!form) {
            return data;
        }

        fields.forEach(function (field) {
            const fieldName = field.trim();

            if (fieldName === '') {
                return;
            }

            const fieldInput = form.elements[fieldName];
            data[fieldName] = fieldInput ? fieldInput.value : '';
        });

        return data;
    }

    function cleanMessages(form) {
        form.querySelectorAll('.input-error-text').forEach(function (message) {
            message.remove();
        });

        form.querySelectorAll('.input-error').forEach(function (input) {
            input.classList.remove('input-error');
        });
    }

    function getErrorText(type, input) {
        if (type === 'date' && input.dataset.compare !== undefined) {
            return 'A data inicial tem de ser anterior a data final.';
        } else if (type === 'unique') {
            const fieldName = input.getAttribute('name');
            const uniqueType = validators.unique.types[fieldName];

            return uniqueType ? uniqueType.message : validators.unique.message;
        } else {
            return validators[type].message;
        }
    }

    function showErrorMessage(input, message) {
        const container = getErrorContainer(input);
        const error = document.createElement('span');

        removeNextErrorMessage(container);

        container.classList.add('input-error');
        error.className = 'input-error-text';
        error.textContent = message;
        container.insertAdjacentElement('afterend', error);
    }

    function getErrorContainer(input) {
        const inputGroup = input.closest('.input-group');

        if (inputGroup) {
            return inputGroup;
        }

        if (input.classList.contains('select2-hidden-accessible')) {
            const nextElement = input.nextElementSibling;

            if (nextElement && nextElement.classList.contains('select2')) {
                return nextElement;
            }
        }

        return input;
    }

    function removeNextErrorMessage(element) {
        const nextElement = element.nextElementSibling;

        if (nextElement && nextElement.classList.contains('input-error-text')) {
            nextElement.remove();
        }
    }

    function getElements(target) {
        if (target instanceof Element) {
            return [target];
        }

        if (target instanceof NodeList || Array.isArray(target)) {
            return Array.from(target);
        }

        if (typeof target === 'string') {
            return Array.from(document.querySelectorAll(target));
        }

        return [];
    }

    window.validateForm = validateForm;
    window.validateForms = validateForms;
})();
