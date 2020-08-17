<?php
declare(strict_types=1);

namespace App\Impl\VK\Models;

use App\Config\Config;
use App\Helpers\Clock;
use App\Models\Bot;
use App\Models\BotFactory;
use App\Models\BunchAssignment;
use App\Reporter\Reporter;
use App\Storage\LinkMonitor;

class VKBotFactory implements BotFactory
{
    private LinkMonitor $linkMonitor;
    private Config $config;
    private Reporter $reporter;
    private Clock $clock;

    /**
     * @param LinkMonitor $linkMonitor
     * @param Config $config
     * @param Reporter $reporter
     * @param Clock $clock
     */
    public function __construct(
        LinkMonitor $linkMonitor,
        Config $config,
        Reporter $reporter,
        Clock $clock
    ) {
        $this->linkMonitor = $linkMonitor;
        $this->config      = $config;
        $this->reporter    = $reporter;
        $this->clock       = $clock;
    }

    /**
     * @inheritDoc
     * @noinspection NullPointerExceptionInspection
     */
    public function createByBunchAssignment(BunchAssignment $bunchAssignment): Bot
    {
        $bunchProfiles = $bunchAssignment->getBunch()->getProfiles();

        $botIdentificationData = $bunchAssignment->getBotIdentificationData();
        $botId                 = $botIdentificationData->getBotId();
        $botKey                = $botIdentificationData->getBotKey();

        return new VKBot($bunchProfiles, $botKey, $botId, $this->linkMonitor, $this->config, $this->reporter, $this->clock);
    }
}