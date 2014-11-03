# Incomplete var directory support.

In some circumstances, it is considered a common practice to have an incomplete vardir, where binary files can be
missing. An example is when debugging a copy of a production instance without the image files copied.

Starting from 5.4/2014.11, this is handled with an overlay of the `IOService`: `TolerantIOService`. When a requested
file does not exist, it catches the BinaryFileNotFoundException.

If a logger is enabled, an info message will be logged.

When it is supposed to return a `BinaryFile`, it instead returns a `MissingBinaryFile`, so that the callers can still
function.
