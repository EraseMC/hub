# Hub

Hub system for the **Erase Network**, built for PocketMine-MP / Dummy (API 5.x).

## Features

- **Server selector** — open a [jojoe77777/FormAPI](https://github.com/jojoe77777/FormAPI) form (bundled, no external virion required) by tapping an NPC or using the hub compass, then connect to any configured server.
- **Live online counts** — every server's real player count is fetched directly from the server (RakNet unconnected ping) and shown on the NPCs, in the selector form, in the scoreboard and in the welcome message.
- **NPCs** — three configurable NPCs (Practice RU, Practice EU, BedWars RU) that display the live online count above their head and open the connection form on interaction.
- **Scoreboard** — a per-player sidebar mirroring the Practice plugin's scoreboard engine.
- **Localization** — `Translator` + `KnownTranslationKeys` with `en_US` and `ru_RU` locales.
- **Join experience** — sound, title/subtitle and a chat welcome message listing every server's online count.
- **SQLite3** storage for lightweight per-player data.

## Configuration

Everything (servers, NPC locations, lobby spawn, item slot, query interval) is configured in `config.yml`,
generated on first run. Localizable strings live in `l10n/*.ini`.
