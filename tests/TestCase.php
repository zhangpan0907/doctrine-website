<?php

declare(strict_types=1);

namespace Doctrine\Website\Tests;

use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface;
use Doctrine\Website\Application;
use Doctrine\Website\Model\Contributor;
use Doctrine\Website\Model\Project;
use Doctrine\Website\Model\ProjectContributor;
use Doctrine\Website\Repositories\ContributorRepository;
use Doctrine\Website\Repositories\ProjectContributorRepository;
use Doctrine\Website\Repositories\ProjectRepository;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class TestCase extends BaseTestCase
{
    private static ContainerBuilder|null $container = null;

    protected function getContainer(): ContainerBuilder
    {
        if (self::$container === null) {
            self::$container = Application::getContainer('test');
        }

        return self::$container;
    }

    /** @param mixed[] $data */
    protected function createModel(string $repositoryClassName, array $data): object
    {
        $repository = $this->getContainer()->get($repositoryClassName);
        self::assertInstanceOf(ObjectRepositoryInterface::class, $repository);

        $object = $repository->create($repository->getClassName());

        $repository->hydrate($object, $data);

        return $object;
    }

    /** @param mixed[] $data */
    protected function createProject(array $data): Project
    {
        $project = $this->createModel(ProjectRepository::class, $data);
        self::assertInstanceOf(Project::class, $project);

        return $project;
    }

    /** @param mixed[] $data */
    protected function createContributor(array $data): Contributor
    {
        $contributor = $this->createModel(ContributorRepository::class, $data);
        self::assertInstanceOf(Contributor::class, $contributor);

        return $contributor;
    }

    /** @param mixed[] $data */
    protected function createProjectContributor(array $data): ProjectContributor
    {
        $projectContributor = $this->createModel(ProjectContributorRepository::class, $data);
        self::assertInstanceOf(ProjectContributor::class, $projectContributor);

        return $projectContributor;
    }
}
