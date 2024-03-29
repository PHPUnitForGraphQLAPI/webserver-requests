<?php

declare(strict_types=1);

namespace PHPUnitForGraphQLAPI\WebserverRequests;

/**
 * Test that enabling/disabling clients (GraphiQL/Voyager)
 * in Custom Endpoints works well
 */
abstract class AbstractClientPathSettingsWebserverRequestTestCase extends AbstractRequestURLPathSettingsWebserverRequestTestCase
{
    use ClientWebserverRequestTestCaseTrait;

    protected function doTestPathsUpdated(
        string $newPath,
        string $previousPath,
    ): void {
        $this->doTestEnabledOrDisabledPath($newPath, 200, 'text/html', true);
        $this->doTestEnabledOrDisabledPath($previousPath, 404, null, false);
    }

    /**
     * @return array<string,string[]> Array of 1 element: [ ${newPath} ]
     */
    protected function providePathEntries(): array
    {
        return $this->provideClientPathEntries();
    }

    /**
     * @return array<string,string[]> Array of 1 element: [ ${newClientPath} ]
     */
    abstract protected function provideClientPathEntries(): array;
}
