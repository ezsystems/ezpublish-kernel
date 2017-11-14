--
-- Table structure for table `ezdfsfile`
--

DROP TABLE IF EXISTS `ezdfsfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE ezdfsfile (
  `name` text NOT NULL,
  `name_trunk` text NOT NULL,
  `name_hash` varchar(34) NOT NULL DEFAULT '',
  `datatype` varchar(255) NOT NULL DEFAULT 'application/octet-stream',
  `scope` varchar(25) NOT NULL DEFAULT '',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name_hash`),
  KEY `ezdfsfile_name` (`name`(250)),
  KEY `ezdfsfile_name_trunk` (`name_trunk`(250)),
  KEY `ezdfsfile_mtime` (`mtime`),
  KEY `ezdfsfile_expired_name` (`expired`,`name`(250))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- NB!: If you plan to run togheter with legacy bridge, make sure to also add ezdfsfile_cache table, see:
-- https://github.com/ezsystems/ezpublish-legacy/blob/master/kernel/sql/mysql/cluster_dfs_schema.sql#L18
--
