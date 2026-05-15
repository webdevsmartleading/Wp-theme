(function () {
    const config = window.SLSSectionBuilder || {};
    const schemas = config.schemas || {};
    const root = document.getElementById('sls-section-builder');
    const metaKey = config.metaKey || '_sls_page_sections';

    if (!root) {
        return;
    }

    const input = document.getElementById(root.dataset.inputId);
    let sections = Array.isArray(config.sections) ? config.sections : [];

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char];
        });
    }

    function getDefaults(type) {
        const schema = schemas[type] || {};
        const defaults = {};

        Object.keys(schema.fields || {}).forEach(function (fieldKey) {
            defaults[fieldKey] = schema.fields[fieldKey].default || '';
        });

        return defaults;
    }

    function syncInput() {
        const value = JSON.stringify(sections);

        if (input) {
            input.value = value;
        }

        if (window.wp && window.wp.data && typeof window.wp.data.dispatch === 'function') {
            try {
                const editorStore = window.wp.data.select('core/editor');

                if (editorStore && window.wp.data.dispatch('core/editor').editPost) {
                    window.wp.data.dispatch('core/editor').editPost({
                        meta: {
                            [metaKey]: value,
                        },
                    });
                }
            } catch (error) {
                // Classic editor saves through the hidden input instead.
            }
        }
    }

    function updateSetting(index, key, value) {
        sections[index].settings = sections[index].settings || {};
        sections[index].settings[key] = value;
        syncInput();
    }

    function moveSection(index, direction) {
        const nextIndex = index + direction;

        if (nextIndex < 0 || nextIndex >= sections.length) {
            return;
        }

        const current = sections[index];
        sections[index] = sections[nextIndex];
        sections[nextIndex] = current;
        render();
    }

    function removeSection(index) {
        sections.splice(index, 1);
        render();
    }

    function addSection(type) {
        if (!schemas[type]) {
            return;
        }

        sections.push({
            id: type + '-' + Date.now(),
            type: type,
            settings: getDefaults(type),
        });

        render();
    }

    function openMediaFrame(index, key, labelNode) {
        if (!window.wp || !window.wp.media) {
            return;
        }

        const frame = window.wp.media({
            title: config.mediaTitle || 'Select image',
            button: {
                text: config.mediaButton || 'Use this image',
            },
            multiple: false,
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            updateSetting(index, key, attachment.id || 0);
            labelNode.textContent = attachment.id ? 'Attachment ID: ' + attachment.id : '';
        });

        frame.open();
    }

    function createField(section, index, fieldKey, field) {
        const value = section.settings && section.settings[fieldKey] !== undefined ? section.settings[fieldKey] : field.default || '';
        const wrapper = document.createElement('div');
        const fieldId = 'sls-field-' + section.id + '-' + fieldKey;
        wrapper.className = 'sls-builder-field';

        const label = document.createElement('label');
        label.setAttribute('for', fieldId);
        label.textContent = field.label || fieldKey;
        wrapper.appendChild(label);

        if (field.type === 'textarea' || field.type === 'css') {
            const textarea = document.createElement('textarea');
            textarea.id = fieldId;
            textarea.placeholder = field.placeholder || '';
            textarea.value = value;
            if (field.type === 'css') {
                wrapper.className += ' sls-builder-field--css';
                textarea.spellcheck = false;
            }
            textarea.addEventListener('input', function () {
                updateSetting(index, fieldKey, textarea.value);
            });
            wrapper.appendChild(textarea);
            return wrapper;
        }

        if (field.type === 'select') {
            const select = document.createElement('select');
            select.id = fieldId;

            Object.keys(field.options || {}).forEach(function (optionKey) {
                const option = document.createElement('option');
                option.value = optionKey;
                option.textContent = field.options[optionKey];
                option.selected = optionKey === value;
                select.appendChild(option);
            });

            select.addEventListener('change', function () {
                updateSetting(index, fieldKey, select.value);
            });
            wrapper.appendChild(select);
            return wrapper;
        }

        if (field.type === 'image') {
            const imageControls = document.createElement('div');
            const chooseButton = document.createElement('button');
            const clearButton = document.createElement('button');
            const valueLabel = document.createElement('span');

            imageControls.className = 'sls-image-field';
            chooseButton.type = 'button';
            chooseButton.className = 'button';
            chooseButton.textContent = (config.i18n && config.i18n.choose) || 'Choose image';
            clearButton.type = 'button';
            clearButton.className = 'button button-link-delete';
            clearButton.textContent = (config.i18n && config.i18n.clear) || 'Clear';
            valueLabel.className = 'sls-image-field__value';
            valueLabel.textContent = value ? 'Attachment ID: ' + value : '';

            chooseButton.addEventListener('click', function () {
                openMediaFrame(index, fieldKey, valueLabel);
            });

            clearButton.addEventListener('click', function () {
                updateSetting(index, fieldKey, 0);
                valueLabel.textContent = '';
            });

            imageControls.appendChild(chooseButton);
            imageControls.appendChild(clearButton);
            imageControls.appendChild(valueLabel);
            wrapper.appendChild(imageControls);
            return wrapper;
        }

        const inputField = document.createElement('input');
        inputField.id = fieldId;
        inputField.type = ['color', 'url', 'number'].includes(field.type) ? field.type : 'text';
        inputField.placeholder = field.placeholder || '';
        inputField.value = value;
        inputField.addEventListener('input', function () {
            updateSetting(index, fieldKey, inputField.value);
        });
        wrapper.appendChild(inputField);

        return wrapper;
    }

    function createCard(section, index) {
        const schema = schemas[section.type] || {};
        const card = document.createElement('article');
        const header = document.createElement('div');
        const titleWrap = document.createElement('div');
        const title = document.createElement('h3');
        const description = document.createElement('p');
        const actions = document.createElement('div');
        const body = document.createElement('div');

        card.className = 'sls-section-card';
        header.className = 'sls-section-card__header';
        title.className = 'sls-section-card__title';
        description.className = 'sls-section-card__description';
        actions.className = 'sls-section-card__actions';
        body.className = 'sls-section-card__body';

        title.textContent = schema.name || section.type;
        description.textContent = schema.description || '';
        titleWrap.appendChild(title);
        titleWrap.appendChild(description);

        [
            { text: (config.i18n && config.i18n.moveUp) || 'Move up', action: function () { moveSection(index, -1); }, disabled: index === 0 },
            { text: (config.i18n && config.i18n.moveDown) || 'Move down', action: function () { moveSection(index, 1); }, disabled: index === sections.length - 1 },
            { text: (config.i18n && config.i18n.remove) || 'Remove', action: function () { removeSection(index); }, danger: true },
        ].forEach(function (buttonConfig) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = buttonConfig.danger ? 'button button-link-delete' : 'button';
            button.textContent = buttonConfig.text;
            button.disabled = Boolean(buttonConfig.disabled);
            button.addEventListener('click', buttonConfig.action);
            actions.appendChild(button);
        });

        Object.keys(schema.fields || {}).forEach(function (fieldKey) {
            body.appendChild(createField(section, index, fieldKey, schema.fields[fieldKey]));
        });

        header.appendChild(titleWrap);
        header.appendChild(actions);
        card.appendChild(header);
        card.appendChild(body);

        return card;
    }

    function renderToolbar() {
        const toolbar = document.createElement('div');
        const select = document.createElement('select');
        const addButton = document.createElement('button');

        toolbar.className = 'sls-builder-toolbar';

        Object.keys(schemas).forEach(function (type) {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = schemas[type].name || type;
            select.appendChild(option);
        });

        addButton.type = 'button';
        addButton.className = 'button button-primary';
        addButton.textContent = (config.i18n && config.i18n.addSection) || 'Add section';
        addButton.addEventListener('click', function () {
            addSection(select.value);
        });

        toolbar.appendChild(select);
        toolbar.appendChild(addButton);

        return toolbar;
    }

    function render() {
        root.innerHTML = '';
        root.appendChild(renderToolbar());

        if (!sections.length) {
            const empty = document.createElement('div');
            empty.className = 'sls-builder-empty';
            empty.innerHTML = escapeHtml((config.i18n && config.i18n.empty) || 'No sections added yet.');
            root.appendChild(empty);
        } else {
            sections.forEach(function (section, index) {
                root.appendChild(createCard(section, index));
            });
        }

        syncInput();
    }

    render();
})();
