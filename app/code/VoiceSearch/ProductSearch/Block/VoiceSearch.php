<?php

namespace VoiceSearch\ProductSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlInterface;

class VoiceSearch extends Template
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Get AJAX search URL
     *
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->urlBuilder->getUrl('voicesearch/index/search');
    }

    /**
     * Check if Web Speech API is supported
     *
     * @return bool
     */
    public function isVoiceSearchEnabled()
    {
        // Always return true as we handle browser compatibility in JavaScript
        return true;
    }

    /**
     * Get module configuration
     *
     * @return array
     */
    public function getVoiceSearchConfig()
    {
        return [
            'searchUrl' => $this->getSearchUrl(),
            'language' => 'en-US', // Default language, can be made configurable
            'maxResults' => 10,
            'continuous' => false,
            'interimResults' => true
        ];
    }
}