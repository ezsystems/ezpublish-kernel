# Internal decorator for Symfony Cache TagAwareInterface

Handles making sure the internal in-memory cache is is also cleared when internal and external code is
deleting/invalidating/clearing cache, and defers invalidation during transactions towards the shared symfony cache pool.
