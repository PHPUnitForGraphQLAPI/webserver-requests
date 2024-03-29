<?php

declare(strict_types=1);

namespace PHPUnitForGraphQLAPI\WebserverRequests;

use GraphQLByPoP\GraphQLServer\Unit\FixtureQueryExecutionGraphQLServerTestCaseTrait;
use GraphQLByPoP\GraphQLServer\Unit\FixtureTestCaseTrait;

use function file_get_contents;

/**
 * Test that enabling/disabling a module works well.
 */
abstract class AbstractFixtureEnableDisableModuleWordPressAuthenticatedUserWebserverRequestTestCase extends AbstractEnableDisableModuleWordPressAuthenticatedUserWebserverRequestTestCase
{
    use FixtureTestCaseTrait;
    use FixtureQueryExecutionGraphQLServerTestCaseTrait;

    public function getDataSetAsString(bool $includeData = true): string
    {
        return $this->addFixtureFolderInfo(parent::getDataSetAsString($includeData));
    }

    /**
     * @return array<string,array<string,mixed>> An array of [$moduleName => ['query' => "...", 'response-enabled' => "...", 'response-disabled' => "..."]]
     */
    protected function getModuleNameEntries(): array
    {
        $moduleEntries = [];
        $fixtureFolder = $this->getFixtureFolder();

        $graphQLQueryFileNameFileInfos = $this->findFilesInDirectory(
            $fixtureFolder,
            ['*.gql'],
            ['*.disabled.gql']
        );

        $moduleEntries = [];
        foreach ($graphQLQueryFileNameFileInfos as $graphQLQueryFileInfo) {
            $query = $graphQLQueryFileInfo->getContents();

            /**
             * From the GraphQL query file name, generate the remaining file names
             */
            $fileName = $graphQLQueryFileInfo->getFilenameWithoutExtension();
            $filePath = $graphQLQueryFileInfo->getPath();
            $moduleEnabledGraphQLResponseFile = $this->getGraphQLResponseFile($filePath, $fileName . ':enabled');
            if (!\file_exists($moduleEnabledGraphQLResponseFile)) {
                $this->throwFileNotExistsException($moduleEnabledGraphQLResponseFile);
            }
            $moduleDisabledGraphQLResponseFile = $this->getGraphQLResponseFile($filePath, $fileName . ':disabled');
            if (!\file_exists($moduleDisabledGraphQLResponseFile)) {
                $this->throwFileNotExistsException($moduleDisabledGraphQLResponseFile);
            }

            // The module name is created by the folder (module vendor) + fileName (module name)
            $moduleVendor = substr($filePath, strlen($fixtureFolder . '/'));
            $moduleName = $moduleVendor . '/' . $fileName;
            $moduleEntries[$moduleName] = [
                'query' => $query,
                'response-enabled' => file_get_contents($moduleEnabledGraphQLResponseFile),
                'response-disabled' => file_get_contents($moduleDisabledGraphQLResponseFile),
                'endpoint' => $this->getModuleEndpoint($fileName),
            ];
        }
        return $this->customizeModuleEntries($moduleEntries);
    }

    /**
     * @param array<string,array<string,mixed>> $moduleEntries
     * @return array<string,array<string,mixed>>
     */
    protected function customizeModuleEntries(array $moduleEntries): array
    {
        return $moduleEntries;
    }

    protected function getModuleEndpoint(string $fileName): ?string
    {
        return $this->getEndpoint();
    }

    /**
     * The combination of folder and filename create the moduleID
     */
    protected function getModuleID(string $dataName): string
    {
        return str_replace(
            ['/', ':enabled', ':disabled'],
            ['_', '', ''],
            $dataName
        );
    }
}
