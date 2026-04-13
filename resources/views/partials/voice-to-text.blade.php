<script>
    (function () {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            return;
        }

        const selector = [
            'textarea',
            'input[type="text"]',
            'input[type="search"]',
            'input[type="email"]',
            'input[type="url"]',
            'input[type="tel"]',
        ].join(',');

        const activeRecognition = new WeakMap();

        function insertAtCursor(field, text) {
            const insertion = text || '';
            const start = typeof field.selectionStart === 'number' ? field.selectionStart : field.value.length;
            const end = typeof field.selectionEnd === 'number' ? field.selectionEnd : field.value.length;
            const currentValue = field.value || '';

            field.value = currentValue.slice(0, start) + insertion + currentValue.slice(end);
            const cursor = start + insertion.length;

            if (typeof field.setSelectionRange === 'function') {
                field.setSelectionRange(cursor, cursor);
            }

            field.dispatchEvent(new Event('input', { bubbles: true }));
        }

        function resetButton(button) {
            button.textContent = 'Mic';
            button.setAttribute('aria-pressed', 'false');
            button.classList.remove('bb-vtt-button--listening');
        }

        function setListening(button) {
            button.textContent = 'Stop';
            button.setAttribute('aria-pressed', 'true');
            button.classList.add('bb-vtt-button--listening');
        }

        function attachButton(field) {
            if (!(field instanceof HTMLElement)) {
                return;
            }

            if (field.dataset.bbVoiceAttached === 'true' || field.disabled || field.readOnly) {
                return;
            }

            field.dataset.bbVoiceAttached = 'true';

            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = 'Mic';
            button.className = 'bb-vtt-button';
            button.setAttribute('aria-label', 'Start voice to text');
            button.setAttribute('aria-pressed', 'false');
            button.title = 'Voice to text';

            field.insertAdjacentElement('afterend', button);

            button.addEventListener('click', () => {
                const current = activeRecognition.get(field);

                if (current) {
                    current.stop();
                    activeRecognition.delete(field);
                    resetButton(button);
                    return;
                }

                const recognition = new SpeechRecognition();
                recognition.lang = document.documentElement.lang || 'en-US';
                recognition.continuous = true;
                recognition.interimResults = true;

                let finalBuffer = '';
                let lastFinalValue = field.value || '';

                recognition.onstart = () => {
                    lastFinalValue = field.value || '';
                    setListening(button);
                };

                recognition.onresult = (event) => {
                    let interimText = '';

                    for (let i = event.resultIndex; i < event.results.length; i += 1) {
                        const transcript = event.results[i][0]?.transcript || '';
                        if (event.results[i].isFinal) {
                            finalBuffer += transcript + ' ';
                        } else {
                            interimText += transcript;
                        }
                    }

                    const combined = (finalBuffer + interimText).trim();
                    field.value = lastFinalValue + (combined ? ` ${combined}` : '');
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                };

                recognition.onerror = () => {
                    recognition.stop();
                };

                recognition.onend = () => {
                    const finalText = finalBuffer.trim();
                    field.value = lastFinalValue;
                    if (finalText) {
                        insertAtCursor(field, ` ${finalText}`);
                    }
                    activeRecognition.delete(field);
                    resetButton(button);
                };

                activeRecognition.set(field, recognition);
                recognition.start();
            });
        }

        function scanFields(root) {
            const scope = root instanceof HTMLElement || root instanceof Document ? root : document;
            scope.querySelectorAll(selector).forEach(attachButton);
        }

        document.addEventListener('DOMContentLoaded', () => {
            scanFields(document);

            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (!(node instanceof HTMLElement)) {
                            return;
                        }

                        if (node.matches(selector)) {
                            attachButton(node);
                        }

                        scanFields(node);
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true,
            });
        });
    })();
</script>

<style>
    .bb-vtt-button {
        margin-top: 0.4rem;
        margin-left: 0.2rem;
        border: 1px solid rgba(14, 116, 144, 0.35);
        border-radius: 9999px;
        padding: 0.2rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #0f172a;
        background: #f0fdfa;
        cursor: pointer;
    }

    .bb-vtt-button:hover {
        background: #cffafe;
    }

    .bb-vtt-button--listening {
        background: #fef2f2;
        border-color: rgba(220, 38, 38, 0.5);
        color: #991b1b;
    }
</style>
