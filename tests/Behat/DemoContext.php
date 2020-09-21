<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\DataFixtures\AppFixtures;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Context\RestContext;
use Coduo\PHPMatcher\Factory\MatcherFactory;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

final class DemoContext extends RestContext
{
    const USERS = [
        "administrator" => ["fozeu.jm@gmail.com" => "Je@nm@rie1234"]
    ];
    const AUTH_URL = "api/login_check";
    const AUTH_JSON = '{
        "username": "%s",
        "password": "%s"
    }';
    /**
     * @var AppFixtures
     */
    private $appFixtures;

    private $matcher;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(\Behatch\HttpCall\Request $request,
                                AppFixtures $appFixtures, EntityManagerInterface $entityManager)
    {
        parent::__construct($request);
        $this->appFixtures = $appFixtures;
        $this->matcher = (new MatcherFactory())->createMatcher();
        $this->entityManager = $entityManager;
    }

    /**
     * @BeforeScenario @createSchema
     */
    public function createSchema()
    {
        //get entity metadata
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();

        //drop and create schema
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        //load fixtures
        $purger = new ORMPurger($this->entityManager);
        $fixturesExecutor = new ORMExecutor($this->entityManager, $purger);
        $fixturesExecutor->execute([
            $this->appFixtures,
        ], true);
    }

    /**
     * @Given I am authenticated as :user
     */
    public function iAmAuthenticatedAs($user)
    {
        $this->request->setHttpHeader('Content-Type', 'application/ld+json');
        $this->request->send(
            'POST',
            $this->locatePath(self::AUTH_URL),
            [],
            [],
            sprintf(self::AUTH_JSON, array_key_first(self::USERS[$user]), self::USERS[$user][array_key_first(self::USERS[$user])])
        );

        $json = json_decode($this->request->getContent(), true);
        // Make sure the token was returned
        $this->assertTrue(isset($json['token']));

        $token = $json['token'];

        $this->request->setHttpHeader(
            'Authorization',
            'Bearer '.$token
        );
    }

    /**
     * @Then the JSON matches expected template:
     */
    public function theJsonMatchesExpectedTemplate(PyStringNode $json)
    {
        $actual = $this->request->getContent();
        //var_dump($actual);
        $this->assertTrue(
            $this->matcher->match($actual, $json->getRaw())
        );
    }

    /**
     * @BeforeScenario @image
     */
    public function prepareImages()
    {
        copy(
            __DIR__.'\..\..\features\fixtures\Stewie.jpg',
            __DIR__.'\..\..\features\fixtures\files\Stewie.jpg'
        );
    }
}
