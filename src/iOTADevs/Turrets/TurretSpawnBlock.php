<?php

/**
 *
 * d8b  .d88888b. 88888888888     d8888 8888888b.
 * Y8P d88P" "Y88b    888        d88888 888  "Y88b
 *     888     888    888       d88P888 888    888
 * 888 888     888    888      d88P 888 888    888  .d88b.  888  888 .d8888b
 * 888 888     888    888     d88P  888 888    888 d8P  Y8b 888  888 88K
 * 888 888     888    888    d88P   888 888    888 88888888 Y88  88P "Y8888b.
 * 888 Y88b. .d88P    888   d8888888888 888  .d88P Y8b.      Y8bd8P       X88
 * 888  "Y88888P"     888  d88P     888 8888888P"   "Y8888    Y88P    88888P'
 *
 * This Program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with TeaSpoon.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author iOTADevs
 * @link http://iotadevs.github.io
 *
 *
 * Most of the code of this Turret plugin is by @CortexPE :joy:
 */

declare(strict_types = 1);

namespace iOTADevs\Turrets;


use pocketmine\block\Block;
use pocketmine\block\GlowingObsidian;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TurretSpawnBlock extends GlowingObsidian {
	public function getName() : string{
		return "Spawn Turret";
	}

	public function onActivate(Item $item, Player $player = null): bool{
		if($item->getId() == Item::NETHER_STAR){
			$this->getLevel()->setBlock($this->asVector3(), Block::get(Block::AIR));
			Main::spawnTurret($this->asVector3(), $this->getLevel(), [$player->getName()]);
			$player->addTitle(TextFormat::DARK_RED . "Turret " . TextFormat::RESET, "Spawned");
			if($player->getGamemode() == Player::SURVIVAL){
				$item->count--;
			}
		}
		return true;
	}
}