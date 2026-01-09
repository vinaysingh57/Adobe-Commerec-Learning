define([
    'jquery',
    'mage/url',
    'mage/translate'
], function ($, urlBuilder, $t) {
    'use strict';

    return function (config, element) {
        var $widget = $(element);
        var $widgetBtn = $widget.find('#voice-search-widget-btn');
        var $modal = $widget.find('#voice-search-widget-modal');
        var $modalClose = $widget.find('#voice-search-modal-close');
        var $voiceBtn = $widget.find('#voice-widget-search-btn');
        var $status = $widget.find('#voice-widget-status');
        var $transcript = $widget.find('#voice-widget-transcript');
        var $results = $widget.find('#voice-widget-results');
        var $error = $widget.find('#voice-widget-error');
        
        var recognition = null;
        var isListening = false;
        var finalTranscript = '';

        // Check for Web Speech API support
        function checkBrowserSupport() {
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                showError($t('Web Speech API is not supported in your browser. Please use Google Chrome.'));
                $voiceBtn.prop('disabled', true);
                return false;
            }
            return true;
        }

        // Initialize Speech Recognition
        function initSpeechRecognition() {
            if (!checkBrowserSupport()) {
                return;
            }

            var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            
            recognition.continuous = config.continuous || false;
            recognition.interimResults = config.interimResults || true;
            recognition.lang = config.language || 'en-US';
            recognition.maxAlternatives = 1;

            recognition.onstart = function() {
                isListening = true;
                $voiceBtn.addClass('listening');
                $voiceBtn.find('.voice-text').text($t('Listening...'));
                showStatus($t('Listening... Please speak now.'));
                hideError();
                hideResults();
            };

            recognition.onresult = function(event) {
                var interimTranscript = '';
                finalTranscript = '';

                for (var i = event.resultIndex; i < event.results.length; i++) {
                    var transcript = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        finalTranscript += transcript;
                    } else {
                        interimTranscript += transcript;
                    }
                }

                var displayTranscript = finalTranscript || interimTranscript;
                if (displayTranscript) {
                    showTranscript(displayTranscript);
                }

                if (finalTranscript) {
                    searchProducts(finalTranscript.trim());
                }
            };

            recognition.onerror = function(event) {
                console.error('Speech recognition error:', event.error);
                var errorMessage = '';
                
                switch (event.error) {
                    case 'no-speech':
                        errorMessage = $t('No speech was detected. Please try again.');
                        break;
                    case 'audio-capture':
                        errorMessage = $t('Microphone is not accessible. Please check your microphone settings.');
                        break;
                    case 'not-allowed':
                        errorMessage = $t('Microphone permission denied. Please allow microphone access and try again.');
                        break;
                    case 'network':
                        errorMessage = $t('Network error occurred. Please check your internet connection.');
                        break;
                    default:
                        errorMessage = $t('An error occurred: ') + event.error;
                }
                
                showError(errorMessage);
                resetButton();
            };

            recognition.onend = function() {
                resetButton();
            };
        }

        // Toggle modal visibility
        function toggleModal() {
            if ($modal.is(':visible')) {
                $modal.hide();
                resetButton();
                if (recognition && isListening) {
                    recognition.stop();
                }
            } else {
                $modal.show();
            }
        }

        // Start/Stop voice recognition
        function toggleVoiceRecognition() {
            if (!recognition) {
                initSpeechRecognition();
            }

            if (isListening) {
                recognition.stop();
            } else {
                recognition.start();
            }
        }

        // Reset button state
        function resetButton() {
            isListening = false;
            $voiceBtn.removeClass('listening');
            $voiceBtn.find('.voice-text').text($t('Start Listening'));
            hideStatus();
        }

        // Show status message
        function showStatus(message) {
            $status.find('.status-text').text(message);
            $status.show();
        }

        // Hide status message
        function hideStatus() {
            $status.hide();
        }

        // Show transcript
        function showTranscript(transcript) {
            $transcript.find('.transcript-text').text(transcript);
            $transcript.show();
        }

        // Show error message
        function showError(message) {
            $error.find('.error-message').text(message);
            $error.show();
        }

        // Hide error message
        function hideError() {
            $error.hide();
        }

        // Show results
        function showResults(data) {
            var $resultsList = $results.find('.results-list');
            $resultsList.empty();

            if (data.products && data.products.length > 0) {
                $.each(data.products, function(index, product) {
                    var resultHtml = 
                        '<a href="' + product.url + '" class="result-item">' +
                            '<img src="' + product.image + '" alt="' + product.name + '" />' +
                            '<div class="result-info">' +
                                '<div class="result-name">' + product.name + '</div>' +
                                '<div class="result-price">' + product.price + '</div>' +
                            '</div>' +
                        '</a>';
                    $resultsList.append(resultHtml);
                });
                $results.show();
            } else {
                showError($t('No products found for your search. Please try different keywords.'));
            }
        }

        // Hide results
        function hideResults() {
            $results.hide();
        }

        // Search products via AJAX
        function searchProducts(query) {
            showStatus($t('Searching for products...'));
            
            $.ajax({
                url: config.searchUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    q: query,
                    form_key: $.mage.cookies.get('form_key')
                },
                success: function(response) {
                    hideStatus();
                    if (response.success) {
                        showResults(response);
                    } else {
                        showError(response.message || $t('Search failed. Please try again.'));
                    }
                },
                error: function(xhr, status, error) {
                    hideStatus();
                    console.error('AJAX Error:', error);
                    showError($t('Search request failed. Please try again.'));
                }
            });
        }

        // Initialize the widget
        function init() {
            if (checkBrowserSupport()) {
                // Widget button click
                $widgetBtn.on('click', function(e) {
                    e.preventDefault();
                    toggleModal();
                });

                // Modal close button
                $modalClose.on('click', function() {
                    toggleModal();
                });

                // Voice search button
                $voiceBtn.on('click', toggleVoiceRecognition);

                // Close modal when clicking outside
                $modal.on('click', function(e) {
                    if (e.target === $modal[0]) {
                        toggleModal();
                    }
                });

                // Close modal on escape key
                $(document).on('keydown', function(e) {
                    if (e.keyCode === 27 && $modal.is(':visible')) {
                        toggleModal();
                    }
                });

                // Initialize speech recognition
                initSpeechRecognition();
            }
        }

        // Initialize when DOM is ready
        $(document).ready(function() {
            init();
        });
    };
});