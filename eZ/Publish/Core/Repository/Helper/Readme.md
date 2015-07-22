# Helpers

Collection of light mappers, helpers and services meant for use in Repository
and/or RepositoryServices.

Given their use they can not rely on Repository or RepositoryServices as
that will lead to cyclic dependencies, they can only rely on SPI and other helpers.
