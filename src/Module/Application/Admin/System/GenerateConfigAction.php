<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2020 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=0);

namespace Ampache\Module\Application\Admin\System;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Config\ConfigurationKeyEnum;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\AccessLevelEnum;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\System\InstallationHelperInterface;
use Ampache\Module\Util\Horde_Browser;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GenerateConfigAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'generate_config';

    private ConfigContainerInterface $configContainer;

    private Horde_Browser $browser;

    private InstallationHelperInterface $installationHelper;

    private UiInterface $ui;

    public function __construct(
        ConfigContainerInterface $configContainer,
        Horde_Browser $browser,
        InstallationHelperInterface $installationHelper,
        UiInterface $ui
    ) {
        $this->configContainer    = $configContainer;
        $this->browser            = $browser;
        $this->installationHelper = $installationHelper;
        $this->ui                 = $ui;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        if (
            $gatekeeper->mayAccess(AccessLevelEnum::TYPE_INTERFACE, AccessLevelEnum::LEVEL_ADMIN) === false ||
            $this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::DEMO_MODE) === true
        ) {
            $this->ui->accessDenied();

            return null;
        }

        $path = __DIR__ . '/../../../../../config/ampache.cfg.php';

        ob_end_clean();
        $current = parse_ini_file($path);
        $final   = $this->installationHelper->generate_config($current);
        $this->browser->downloadHeaders(
            'ampache.cfg.php',
            'text/plain',
            false,
            filesize($path)
        );
        echo $final;

        return null;
    }
}