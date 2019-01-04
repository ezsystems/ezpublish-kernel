# In Memory cache classes


Classes for specific in-memory usage, at this level only in-frequently updated metadata is meant to be cached like this.
Examples: Languages, ContentTypes, ObjectStates, Sections


By design these are meant to be also used within Storage Engines so Persistence cache and Backend can share in-memory
cache.
