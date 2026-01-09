define([
    'jquery',
    'mage/url',
    'mage/translate'
], function ($, urlBuilder, $t) {
    'use strict';

    return function (config, element) {
        var $container = $(element);
        var $voiceBtn = $container.find('#voice-search-btn');
        var $status = $container.find('#voice-search-status');
        var $transcript = $container.find('#voice-search-transcript');
        var $results = $container.find('#voice-search-results');
        var $error = $container.find('#voice-search-error');
        
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
                $voiceBtn.find('.voice-search-text').text($t('Listening...'));
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
            $voiceBtn.find('.voice-search-text').text($t('Start Voice Search'));
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
            var $resultsContainer = $results.find('.results-container');
            $resultsContainer.empty();

            if (data.products && data.products.length > 0) {
                $.each(data.products, function(index, product) {
                    var productHtml = 
                        '<div class="product-item">' +
                            '<a href="' + product.url + '" class="product-link">' +
                                '<img src="' + product.image + '" alt="' + product.name + '" />' +
                                '<div class="product-name">' + product.name + '</div>' +
                                '<div class="product-price">' + product.price + '</div>' +
                            '</a>' +
                        '</div>';
                    $resultsContainer.append(productHtml);
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

        // Initialize the component
        function init() {
            if (checkBrowserSupport()) {
                $voiceBtn.on('click', toggleVoiceRecognition);
                
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