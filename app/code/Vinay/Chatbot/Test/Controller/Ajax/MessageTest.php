<?php
namespace Vinay\Chatbot\Test\Controller\Ajax;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Vinay\Chatbot\Controller\Ajax\Message;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;

class MessageTest extends TestCase
{
    public function testExecuteReturnsJsonResponse()
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->createMock(Context::class);
        $resultFactoryMock = $this->createMock(ResultFactory::class);
        $requestMock = $this->createMock(RequestInterface::class);
        
        $contextMock->method('getRequest')->willReturn($requestMock);
        $resultMock = $this->getMockBuilder('Magento\\Framework\\Controller\\Result\\Json')
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock->method('create')->willReturn($resultMock);
        
        $requestMock->method('getParam')->with('message')->willReturn('Hello');
        $resultMock->expects($this->once())
            ->method('setData')
            ->with($this->callback(function($data) {
                return isset($data['response']);
            }));
        
        $controller = $objectManager->getObject(
            Message::class,
            [
                'context' => $contextMock,
                'resultFactory' => $resultFactoryMock
            ]
        );
        $controller->resultFactory = $resultFactoryMock;
        $controller->execute();
    }
    
    public function testExecuteHandlesApiError()
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->createMock(Context::class);
        $resultFactoryMock = $this->createMock(ResultFactory::class);
        $requestMock = $this->createMock(RequestInterface::class);
        $contextMock->method('getRequest')->willReturn($requestMock);
        $resultMock = $this->getMockBuilder('Magento\\Framework\\Controller\\Result\\Json')
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock->method('create')->willReturn($resultMock);
        $requestMock->method('getParam')->with('message')->willReturn('Hello');
        $resultMock->expects($this->once())
            ->method('setData')
            ->with($this->callback(function($data) {
                return $data['response'] === 'Bot: Sorry, no response.';
            }));

        // Create a partial mock for the controller to override cURL call
        $controller = $this->getMockBuilder(Message::class)
            ->setConstructorArgs([
                'context' => $contextMock,
                'resultFactory' => $resultFactoryMock
            ])
            ->onlyMethods(['callOpenAiApi'])
            ->getMock();
        $controller->method('callOpenAiApi')->willReturn(false);
        $controller->execute();
    }
}
