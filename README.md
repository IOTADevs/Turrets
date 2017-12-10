# Turrets [![Poggit](https://poggit.pmmp.io/ci.shield/IOTADevs/Turrets/~)](https://poggit.pmmp.io/ci/IOTADevs/Turrets/~)
A Portals 2 Inspired Turret plugin for PMMP!

Most of the code here is by @CortexPE :joy:

Usage:
 - In Standalone Mode
   1. Place a Glowing Obsidian Block (/give <player> glowing_obsidian)
   2. Tap / click it with Nether Star
 - In API Mode
   <br />// Friends refer to Player Names the Turret will ignore 
   - Without "friend-ed" player(s) 
    ```php
    \iOTADevs\Turrets\Main::spawnTurret($Vector3Position, $levelObject, []);
    ```
   - With "friend-ed" player(s) 
    ```php
    \iOTADevs\Turrets\Main::spawnTurret($Vector3Position, $levelObject, ["CortexPE","Steve"]);
    ```
