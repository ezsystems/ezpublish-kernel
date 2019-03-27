# Internal In-Memory cache class


Class for specific in-memory usage, at this level only in-frequently updated metadata is
meant to be cached like this.

Mainly: Languages, ContentTypes, ObjectStates, Sections, Roles, Tags
_Optionally also ContentInfo and Locations, but should not be used for Content._
