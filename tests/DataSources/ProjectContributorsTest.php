<?php

declare(strict_types=1);

namespace Doctrine\Website\Tests\DataSources;

use Doctrine\Website\DataBuilder\ProjectContributorDataBuilder;
use Doctrine\Website\DataBuilder\WebsiteData;
use Doctrine\Website\DataBuilder\WebsiteDataReader;
use Doctrine\Website\DataSources\ProjectContributors;
use Doctrine\Website\Model\Project;
use Doctrine\Website\Model\TeamMember;
use Doctrine\Website\Repositories\ProjectRepository;
use Doctrine\Website\Repositories\TeamMemberRepository;
use Doctrine\Website\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ProjectContributorsTest extends TestCase
{
    private WebsiteDataReader&MockObject $dataReader;

    /** @var TeamMemberRepository<TeamMember>&MockObject */
    private TeamMemberRepository&MockObject $teamMemberRepository;

    /** @var ProjectRepository<Project>&MockObject */
    private ProjectRepository&MockObject $projectRepository;

    private ProjectContributors $projectContributors;

    protected function setUp(): void
    {
        $this->dataReader           = $this->createMock(WebsiteDataReader::class);
        $this->teamMemberRepository = $this->createMock(TeamMemberRepository::class);
        $this->projectRepository    = $this->createMock(ProjectRepository::class);

        $this->projectContributors = new ProjectContributors(
            $this->dataReader,
            $this->teamMemberRepository,
            $this->projectRepository,
        );
    }

    public function testGetSourceRows(): void
    {
        $projectContributors = [
            [
                'github' => 'jwage',
                'projectSlug' => 'orm',
            ],
            [
                'github' => 'Ocramius',
                'projectSlug' => 'dbal',
            ],
        ];

        $jwageTeamMember    = $this->createMock(TeamMember::class);
        $ocramiusTeamMember = $this->createMock(TeamMember::class);

        $ormProject  = $this->createMock(Project::class);
        $dbalProject = $this->createMock(Project::class);

        $this->dataReader->expects(self::once())
            ->method('read')
            ->with(ProjectContributorDataBuilder::DATA_FILE)
            ->willReturn(new WebsiteData(ProjectContributorDataBuilder::DATA_FILE, $projectContributors));

        $this->teamMemberRepository->expects(self::exactly(2))
            ->method('findOneByGithub')
            ->willReturnMap([
                ['jwage', $jwageTeamMember],
                ['Ocramius', $ocramiusTeamMember],
            ]);

        $this->projectRepository->expects(self::exactly(2))
            ->method('findOneBySlug')
            ->willReturnMap([
                ['orm', $ormProject],
                ['dbal', $dbalProject],
            ]);

        $rows = $this->projectContributors->getSourceRows();

        $expected = [
            [
                'github' => 'jwage',
                'projectSlug' => 'orm',
                'teamMember' => $jwageTeamMember,
                'project' => $ormProject,
            ],
            [
                'github' => 'Ocramius',
                'projectSlug' => 'dbal',
                'teamMember' => $ocramiusTeamMember,
                'project' => $dbalProject,
            ],
        ];

        self::assertSame($expected, $rows);
    }
}
