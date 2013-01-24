================================
Using the Integration Test Suite
================================

The Integration Test Suite can be used to verify the functionality of the Public
API with different back ends. In general, you just need a working *PHPUnit* in
order to run the tests.

The Integration Test Suite ships with a memory back end, which can deal as a
verification step that the test suite itself runs.

In order to run any of the tests, you need to copy the
``config.php-DEVELOPMENT`` configuration file in the ``ezpublish-kernel``
directory into a ``config.php`` file.

---------------
Memory Back End
---------------

To run the Integration Test Suite against the Memory Back End, you just need to
run ``phpunit`` inside this directory. This should result in a fully successful
run of the suite.

-----------------
Database Back End
-----------------

To run the test suite against the real world implementation, use the alternative
``phpunit-legacy.xml`` as the configuration for PHPUnit.
Beware that you need to have set the correct path to your eZ Publish Legacy instance
in ``config.php``.

After that, use the following command to run the tests::

    phpunit -c phpunit-legacy.xml

Any problems occurring during the run should be issues in the Public API
implementation, as long as `Memory Back End`_ runs correctly.
