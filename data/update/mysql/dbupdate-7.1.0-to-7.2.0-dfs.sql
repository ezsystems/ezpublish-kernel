SET default_storage_engine=InnoDB;

--
-- EZP-28950: MySQL UTF8 doesn't support 4-byte chars
-- This shortens indexes so that 4-byte content can fit.
--

ALTER TABLE `ezdfsfile` DROP KEY `ezdfsfile_name`;
ALTER TABLE `ezdfsfile` ADD KEY `ezdfsfile_name` (`name` (191));

ALTER TABLE `ezdfsfile` DROP KEY `ezdfsfile_name_trunk`;
ALTER TABLE `ezdfsfile` ADD KEY `ezdfsfile_name_trunk` (`name_trunk` (191));

ALTER TABLE `ezdfsfile` DROP KEY `ezdfsfile_expired_name`;
ALTER TABLE `ezdfsfile` ADD KEY `ezdfsfile_expired_name` (`expired`, `name` (191));

--
-- NB!: If you use DFS and Legacy Bridge, you should also run this script for the `ezdfsfile_cache` table.
--
