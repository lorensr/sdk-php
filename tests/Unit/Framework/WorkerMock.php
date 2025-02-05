<?php

declare(strict_types=1);

namespace Temporal\Tests\Unit\Framework;

use PHPUnit\Framework\Exception;
use React\Promise\PromiseInterface;
use Temporal\Internal\Queue\QueueInterface;
use Temporal\Internal\Repository\Identifiable;
use Temporal\Internal\ServiceContainer;
use Temporal\Internal\Transport\Router;
use Temporal\Internal\Transport\RouterInterface;
use Temporal\Tests\Unit\Framework\Assertion\WorkflowResult;
use Temporal\Tests\Unit\Framework\Expectation\ActivityCall;
use Temporal\Tests\Unit\Framework\Expectation\Timer;
use Temporal\Tests\Unit\Framework\Requests\StartWorkflow;
use Temporal\Tests\Unit\Framework\Server\CommandHandler\CommandHandlerFactory;
use Temporal\Tests\Unit\Framework\Server\ServerMock;
use Temporal\Worker\DispatcherInterface;
use Temporal\Worker\Transport\Command\RequestInterface;
use Temporal\Worker\Transport\Goridge;
use Temporal\Worker\WorkerInterface;
use Temporal\Worker\WorkerOptions;
use Throwable;

use function get_class;

/**
 * @internal
 */
final class WorkerMock implements Identifiable, WorkerInterface, DispatcherInterface
{
    private string $name;
    private WorkerOptions $options;
    private ServiceContainer $services;
    private RouterInterface $router;
    private ServerMock $server;

    public function __construct(
        string $taskQueue,
        WorkerOptions $options,
        ServiceContainer $serviceContainer
    ) {
        $this->name = $taskQueue;
        $this->options = $options;
        $this->services = $serviceContainer;
        $this->router = $this->createRouter();
        $this->server = new ServerMock(CommandHandlerFactory::create());
    }

    private function createRouter(): RouterInterface
    {
        $router = new Router();
        $router->add(new Router\StartWorkflow($this->services));
        $router->add(new Router\InvokeActivity($this->services, Goridge::create()));
        $router->add(new Router\DestroyWorkflow($this->services->running));

        return $router;
    }

    public function runWorkflow(string $workflowCLass, ...$args): void
    {
        $this->server->addCommand(new StartWorkflow($workflowCLass, ...$args));
    }

    public function waitBatch(): ?CommandBatchMock
    {
        if ($this->server->hasEmptyQueue()) {
            return null;
        }

        return $this->server->getBatch();
    }

    public function send(QueueInterface $commands): void
    {
        foreach ($commands as $command) {
            $result = $this->server->handleCommand($command);
            if ($result !== null) {
                $this->server->addCommand($result);
            }
        }
    }

    /**
     * @throws Throwable
     */
    public function error(Throwable $error): void
    {
        if ($error instanceof Exception) {
            throw $error;
        }
    }

    public function getID(): string
    {
        return $this->name;
    }

    public function getOptions(): WorkerOptions
    {
        return $this->options;
    }

    public function registerWorkflowTypes(string ...$class): WorkerInterface
    {
        foreach ($class as $workflow) {
            $proto = $this->services->workflowsReader->fromClass($workflow);
            $this->services->workflows->add($proto, false);
        }

        return $this;
    }

    public function registerWorkflowObject($object): self
    {
        $proto = $this->services->workflowsReader->fromObject($object);
        $this->services->workflows->add($proto, false);

        return $this;
    }

    public function getWorkflows(): iterable
    {
        return $this->services->workflows;
    }

    public function registerActivityImplementations(object ...$activity): WorkerInterface
    {
        foreach ($activity as $act) {
            $class = get_class($act);

            foreach ($this->services->activitiesReader->fromClass($class) as $proto) {
                $this->services->activities->add($proto->withInstance($act), false);
            }
        }

        return $this;
    }

    public function getActivities(): iterable
    {
        return $this->services->activities;
    }

    public function dispatch(RequestInterface $request, array $headers): PromiseInterface
    {
        return $this->router->dispatch($request, $headers);
    }

    public function expectActivityCall(string $class, string $method, ...$returnValues): void
    {
        $this->server->expect(new ActivityCall($class, $method, $returnValues));
    }

    public function expectTimer(int $seconds): void
    {
        $this->server->expect(new Timer($seconds));
    }

    public function assertWorkflowReturns($value): void
    {
        $this->server->assert(new WorkflowResult($value));
    }

    public function complete(): void
    {
        $this->server->checkWaitingExpectations();
    }
}
