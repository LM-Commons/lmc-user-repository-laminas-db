<?php

declare(strict_types=1);

namespace LmcTest\User\Repository\Db\Adapter;

use Exception as BaseException;
use Laminas\Db\Adapter\Adapter;
use Laminas\EventManager\Event;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\ServiceManager\ServiceManager;
use Lmc\User\Repository\Db\Adapter\AbstractDbAdapter;
use Lmc\User\Repository\Db\Adapter\BaseUserHydratorFactory;
use Lmc\User\Repository\Db\Adapter\Db;
use Lmc\User\Repository\Db\Adapter\UserHydrator;
use Lmc\User\Repository\Db\Entity\User as Entity;
use Lmc\User\Repository\Db\Entity\User;
use Lmc\User\Repository\Db\Options\Options;
use Lmc\User\Repository\UserInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function array_merge;
use function call_user_func_array;
use function constant;
use function defined;
use function explode;
use function file_get_contents;
use function in_array;
use function preg_match;
use function sprintf;
use function strtoupper;
use function ucfirst;

#[CoversClass(Db::class)]
#[CoversClass(AbstractDbAdapter::class)]
final class DbTest extends TestCase
{
    private static bool|Adapter $realAdapter = false;

    private static string $driver;

    protected ContainerInterface $container;

    protected Adapter $adapter;

    protected ?Db $db = null;

    protected UserHydrator $userHydrator;

    protected Entity $entity;

    protected bool $eventCalled           = false;
    protected ?UserInterface $eventEntity = null;
    private ?string $eventName            = null;

    public static function setUpBeforeClass(): void
    {
        // Setup databases
        if (defined('DB_DRIVER')) {
            self::$driver = (string) constant('DB_DRIVER');
            if (in_array(self::$driver, ['sqlite', 'mysql'])) {
                self::setupAdapter(self::$driver);
            }
        }
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function setUp(): void
    {
        $this->container = new ServiceManager([]);
        $this->container->setService(Options::class, new Options([]));
        $factory            = new BaseUserHydratorFactory();
        $baseHydrator       = $factory($this->container, 'foo');
        $this->userHydrator = new UserHydrator($baseHydrator);
        $this->entity       = new Entity();
        if (self::$realAdapter instanceof Adapter) {
            $this->db = new Db(
                self::$realAdapter,
                $this->userHydrator,
                $this->entity,
                'user',
            );
        }
        parent::setUp();
    }

    /**
     * @throws Exception
     */
    public function testConstruct(): void
    {
        $adapter  = $this->createMock(Adapter::class);
        $hydrator = new UserHydrator(new ClassMethodsHydrator());
        $db       = new Db(
            $adapter,
            $hydrator,
            new Entity(),
            'user'
        );
        $this->assertEquals('user', $db->getTableName());
        $this->assertEquals($hydrator, $db->getHydrator());
    }

    #[DataProvider('providerTestFindBy')]
    public function testFindBy(
        string $method,
        array $args,
        bool $expectingResult,
        string $expectedId,
    ): void {
        if (null !== $this->db) {
            $this->setupUserTable(self::$realAdapter, self::$driver);
            $return = call_user_func_array([$this->db, $method], $args);

            if ($expectingResult) {
                $this->assertIsObject($return);
                $this->assertInstanceOf(Entity::class, $return);
                $this->assertEquals($expectedId, $return->getId());
            } else {
                $this->assertNull($return);
            }
        }
    }

    public function testFindEventListener(): void
    {
        if (null !== $this->db) {
            $this->db->getEventManager()->attach('find', [$this, 'onEvent']);

            $this->setupUserTable(self::$realAdapter, self::$driver);
            $this->eventCalled = false;
            $this->eventName   = null;
            $this->eventEntity = null;
            $entity            = $this->db->findById('1');
            $this->assertTrue($this->eventCalled);
            $this->assertEquals('find', $this->eventName);
            $this->assertEquals($entity, $this->eventEntity);
        }
    }

    public function testFindEventListenerNotFound(): void
    {
        if (null !== $this->db) {
            $this->db->getEventManager()->attach('find', [$this, 'onEvent']);

            $this->setupUserTable(self::$realAdapter, self::$driver);
            $this->eventCalled = false;
            $this->eventName   = null;
            $this->eventEntity = null;
            $this->db->findById('4');
            $this->assertTrue($this->eventCalled);
            $this->assertEquals('find', $this->eventName);
            $this->assertNull($this->eventEntity);
        }
    }

    public function testMultipleFind(): void
    {
        if (null !== $this->db) {
            $this->setupUserTable(self::$realAdapter, self::$driver);
            $entity = $this->db->findById(1);
            $this->assertEquals(1, $entity->getId());
            $entity = $this->db->findById(2);
            $this->assertEquals(2, $entity->getId());
        }
    }

    public function testInsert(): void
    {
        if (null !== $this->db) {
            $entity = new Entity();
            $entity->setEmail('foo@bar.com');
            $entity->setUsername('foo');
            $entity->setPassword('foo');
            $entity->setDisplayName('foo');
            $entity->setState(UserInterface::STATE_ACTIVE);

            $this->setupUserTable(self::$realAdapter, self::$driver);
            $this->db->insert($entity);
            $this->assertNotNull($entity->getId());
            $fetchedEntity = $this->db->findById($entity->getId());
            $this->assertEquals($entity, $fetchedEntity);
        }
    }

    public function testUpdate(): void
    {
        if (null !== $this->db) {
            $this->setupUserTable(self::$realAdapter, self::$driver);
            $entity = $this->db->findById(1);
            $entity->setEmail('foo@bar.com');
            $this->db->update($entity);
            $updatedEntity = $this->db->findById(1);
            $this->assertEquals('foo@bar.com', $updatedEntity->getEmail());
        }
    }

    public function testDelete(): void
    {
        if (null !== $this->db) {
            $this->setupUserTable(self::$realAdapter, self::$driver);
            $entity = $this->db->findById(1);
            $this->db->delete($entity);
            $updatedEntity = $this->db->findById(1);
            $this->assertNull($updatedEntity);
        }
    }

    private static function setupAdapter(string $driver): void
    {
        $upCase = strtoupper($driver);
        if (
            ! defined(sprintf('DB_%s_DSN', $upCase))
            || ! defined(sprintf('DB_%s_USERNAME', $upCase))
            || ! defined(sprintf('DB_%s_PASSWORD', $upCase))
            || ! defined(sprintf('DB_%s_SCHEMA', $upCase))
        ) {
            return;
        }

        try {
            $connection = [
                'driver' => sprintf('Pdo_%s', ucfirst($driver)),
                'dsn'    => constant(sprintf('DB_%s_DSN', $upCase)),
            ];
            if (constant(sprintf('DB_%s_USERNAME', $upCase)) !== '') {
                $connection['username'] = (string) constant(sprintf('DB_%s_USERNAME', $upCase));
                $connection['password'] = (string) constant(sprintf('DB_%s_PASSWORD', $upCase));
            }
            $adapter = new Adapter($connection);
            self::setupSqlDatabase($adapter, (string) constant(sprintf('DB_%s_SCHEMA', $upCase)));
            self::$realAdapter = $adapter;
        } catch (BaseException $exception) {
            self::$realAdapter = false;
        }
    }

    private static function setupSqlDatabase(Adapter $adapter): void
    {
        $queryStack = ['DROP TABLE IF EXISTS user'];

        foreach ($queryStack as $query) {
            if (! preg_match('/\S+/', $query)) {
                continue;
            }
            $adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    private function setupUserTable(Adapter $adapter, string $driver): void
    {
        $upCase     = strtoupper($driver);
        $schemaPath = (string) constant(sprintf('DB_%s_SCHEMA', $upCase));
        $queryStack = ['DROP TABLE IF EXISTS user'];
        $queryStack = array_merge($queryStack, explode(';', file_get_contents($schemaPath)));
        $queryStack = array_merge($queryStack, explode(';', file_get_contents(__DIR__ . '/_files/user.sql')));
        foreach ($queryStack as $query) {
            if (! preg_match('/\S+/', $query)) {
                continue;
            }
            $adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    public static function providerTestFindBy(): array
    {
        $user = new User();
        $user->setEmail('lmc-user@github.com');
        $user->setUsername('lmc-user');
        $user->setDisplayName('Lmc-User');
        $user->setId('1');
        $user->setState(1);
        $user->setPassword('lmc-user');

        return [
            'findById = 1'                      => [
                'findById', // method
                ['1'], // method args
                true, //expected
                '1', // id
            ],
            'findByEmail = lmc-user@github.com' => [
                'findByEmail',
                ['lmc-user@github.com'],
                true,
                '1',
            ],
            'findByUsername = lmc-user'         => [
                'findByUsername',
                ['lmc-user'],
                true,
                '1',
            ],
            'findById = 2'                      => [
                'findById',
                ['2'],
                true,
                '2',
            ],
            'findById = 3'                      => [
                'findById',
                ['3'],
                true,
                '3',
            ],
            'findById = 4'                      => [
                'findById',
                ['4'],
                false,
                '0',
            ],
            'findByEmail = foo'                 => [
                'findByEmail',
                ['foo'],
                false,
                '0',
            ],
            'findByUsername = foo'              => [
                'findByUsername',
                ['foo'],
                false,
                '0',
            ],
        ];
    }

    public function onEvent(Event $event): void
    {
        $this->assertEquals('find', $event->getName());
        $this->eventCalled = true;
        $this->eventEntity = $event->getParam('entity');
        $this->eventName   = $event->getName();
    }
}
