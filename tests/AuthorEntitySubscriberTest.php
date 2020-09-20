<?php


namespace App\Tests;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\User;
use App\EventSubscriber\AuthorEntitySubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use Symfony\Component\Security\Core\Security;

class AuthorEntitySubscriberTest extends TestCase
{
    public function testConfiguration()
    {
        $result = AuthorEntitySubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::VIEW, $result);

        $expected = ['getAuthenticatedUser', EventPriorities::PRE_WRITE];
        $this->assertEquals($expected, $result[KernelEvents::VIEW]);
    }

    /**
     * @@dataProvider successDataProviderAuthorCallTest
     */
    public function testSuccessCaseSetAuthorCall($className, $requestMethod)
    {
        $securityMock = $this->getSecurityMock(false);

        $entityMock = $this->getMockEntityObject($className, true);

        $requestMock = $this->getRequestMock($requestMethod);

        $eventMock = $this->getEventMock($entityMock, $requestMock);

        (new AuthorEntitySubscriber($securityMock))->getAuthenticatedUser($eventMock);
    }

    /**
     * @@dataProvider failByWrongEntityDataProviderAuthorCallTest
     */
    public function testFailAuthorCallByWrongEntity($className, $request)
    {
        $securityMock = $this->getSecurityMock(false);

        $entityMock = $this->getMockEntityObject($className, false);

        $requestMock = $this->getRequestMock($request);

        $eventMock = $this->getEventMock($entityMock, $requestMock);

        (new AuthorEntitySubscriber($securityMock))->getAuthenticatedUser($eventMock);
    }

    /**
     * @@dataProvider failByWrongRequestDataProvider
     */
    public function testFailAuthorCallByWrongRequest($className, $requestMethod)
    {
        $securityMock = $this->getSecurityMock(false);

        $entityMock = $this->getMockEntityObject($className, false);

        $requestMock = $this->getRequestMock($requestMethod);

        $eventMock = $this->getEventMock($entityMock, $requestMock);

        (new AuthorEntitySubscriber($securityMock))->getAuthenticatedUser($eventMock);
    }

    public function testFailByNullSecurityGetUser()
    {
        $securityMock = $this->getSecurityMock(true);

        $entityMock = $this->getMockEntityObject(BlogPost::class, false);

        $requestMock = $this->getRequestMock("POST");

        $eventMock = $this->getEventMock($entityMock, $requestMock);

        (new AuthorEntitySubscriber($securityMock))->getAuthenticatedUser($eventMock);
    }

    public function successDataProviderAuthorCallTest(): array
    {
        return [
            [BlogPost::class, "POST"],
            [Comment::class, "POST"]
        ];
    }

    public function failByWrongEntityDataProviderAuthorCallTest(): array
    {
        return [
            [User::class, "POST"],
            [Image::class, "POST"],
            ['NonExisting', "POST"]
        ];
    }

    public function failByWrongRequestDataProvider(): array
    {
        return [
            [BlogPost::class, "GET"],
            [Comment::class, "PUT"],
            [BlogPost::class, "PATCH"],
            [Comment::class, "DELETE"],
            [Comment::class, "DELETE"]
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function getEventMock($entityMock, $requestMock): \PHPUnit\Framework\MockObject\MockObject
    {
        $eventMock = $this->getMockBuilder(ViewEvent::class)
            ->disableOriginalConstructor()->getMock();

        $eventMock->expects($this->once())
            ->method("getControllerResult")
            ->willReturn($entityMock);

        $eventMock->expects($this->once())
            ->method("getRequest")
            ->willReturn($requestMock);

        return $eventMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function getSecurityMock(bool $nullable): \PHPUnit\Framework\MockObject\MockObject
    {
        $securityMock = $this->getMockBuilder(Security::class)->disableOriginalConstructor()->getMock();

        $securityMock->expects($this->once())
            ->method("getUser")
            ->willReturn(!$nullable ? new User() : null);

        return $securityMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function getMockEntityObject($className, bool $once): \PHPUnit\Framework\MockObject\MockObject
    {
        $entityMock = $this->getMockBuilder($className)
            ->setMethods(["setAuthor"])
            ->getMock();
        $entityMock->expects($once ? $this->once() : $this->never())
            ->method("setAuthor");
        return $entityMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function getRequestMock($method): \PHPUnit\Framework\MockObject\MockObject
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->setMethods(["getMethod"])->getMock();
        $requestMock->expects($this->once())
            ->method("getMethod")
            ->willReturn($method);
        return $requestMock;
    }
}