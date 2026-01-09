<?php
namespace Vinay\Chatbot\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;

class Message extends Action
{
    protected $resultFactory;

    public function __construct(
        Context $context,
        ResultFactory $resultFactory
    ) {
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * Call OpenAI API (extracted for testability)
     */
    protected function callOpenAiApi($userMessage)
    {
        $apiKey = 'YOUR_OPENAI_API_KEY'; // Replace with your OpenAI API key
        $apiUrl = 'https://api.openai.com/v1/chat/completions';
        $postData = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $userMessage]
            ],
            'max_tokens' => 100
        ];
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        $apiResponse = curl_exec($ch);
        curl_close($ch);
        return $apiResponse;
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $request = $this->getRequest();
        $userMessage = $request->getParam('message');

        // Use extracted method for API call
        $apiResponse = $this->callOpenAiApi($userMessage);

        $botResponse = 'Bot: Sorry, no response.';
        if ($apiResponse) {
            $responseData = json_decode($apiResponse, true);
            if (isset($responseData['choices'][0]['message']['content'])) {
                $botResponse = $responseData['choices'][0]['message']['content'];
            }
        }

        $result->setData(['response' => $botResponse]);
        return $result;
    }
}
