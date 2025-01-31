<?php

declare(strict_types=1);

namespace Doctrine\Website\Tests\DataSources;

use Doctrine\Website\DataBuilder\ContributorDataBuilder;
use Doctrine\Website\DataBuilder\WebsiteData;
use Doctrine\Website\DataBuilder\WebsiteDataReader;
use Doctrine\Website\DataSources\Contributors;
use Doctrine\Website\Model\Project;
use Doctrine\Website\Model\TeamMember;
use Doctrine\Website\Repositories\ProjectRepository;
use Doctrine\Website\Repositories\TeamMemberRepository;
use Doctrine\Website\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ContributorsTest extends TestCase
{
    private WebsiteDataReader&MockObject $dataReader;

    /** @var TeamMemberRepository<TeamMember>&MockObject */
    private TeamMemberRepository&MockObject $teamMemberRepository;

    /** @var ProjectRepository<Project>&MockObject */
    private ProjectRepository&MockObject $projectRepository;

    private Contributors $contributors;

    protected function setUp(): void
    {
        $this->dataReader           = $this->createMock(WebsiteDataReader::class);
        $this->teamMemberRepository = $this->createMock(TeamMemberRepository::class);
        $this->projectRepository    = $this->createMock(ProjectRepository::class);

        $this->contributors = new Contributors(
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
                'projects' => ['orm'],
            ],
            [
                'github' => 'Ocramius',
                'projects' => ['dbal'],
            ],
        ];

        $jwageTeamMember    = $this->createMock(TeamMember::class);
        $ocramiusTeamMember = $this->createMock(TeamMember::class);

        $ormProject  = $this->createMock(Project::class);
        $dbalProject = $this->createMock(Project::class);

        $this->dataReader->expects(self::once())
            ->method('read')
            ->with(ContributorDataBuilder::DATA_FILE)
            ->willReturn(new WebsiteData(ContributorDataBuilder::DATA_FILE, $projectContributors));

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

        $rows = $this->contributors->getSourceRows();

        self::assertEquals([
            [
                'github' => 'jwage',
                'projects' => [$ormProject],
                'teamMember' => $jwageTeamMember,
            ],
            [
                'github' => 'Ocramius',
                'projects' => [$dbalProject],
                'teamMember' => $ocramiusTeamMember,
            ],
        ], $rows);
    }
}
