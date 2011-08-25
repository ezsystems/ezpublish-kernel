Open topics
===========

Content object relations
------------------------

Summary
~~~~~~~

Conclusion
~~~~~~~~~~


Content object states
---------------------

Summary
~~~~~~~

Conclusion
~~~~~~~~~~


Location URLs
-------------

Summary
~~~~~~~

Conclusion
~~~~~~~~~~


Storage interface
-----------------

Summary
~~~~~~~

Conclusion
~~~~~~~~~~


General questions regarding Fields (typeHint etc.)
--------------------------------------------------

Summary
~~~~~~~

Conclusion
~~~~~~~~~~



Return type boolean vs. Exceptions
----------------------------------

Summary
~~~~~~~
Concrete return types are not always specified. Booleans are returned rather
than a thrown exception for error situations.

Conclusion
~~~~~~~~~~
Initial feeling is that Exceptions should be defined for these methods.




Languages
---------

Summary
~~~~~~~
Need API to deal with languages to be able to map language id's to languageCode

Conclusion
~~~~~~~~~~



Translations
------------

Summary
~~~~~~~
Content DO:
  Need translation support and some changes to ContentHandler api to reflect
  translation needs and workflow(s).

Properties in several languages (->name / -> description):
  Need a more sexy api to not have to deal with raw array structures in DO.
  Options: add a ->setLanguage( $langCode ) api or change properties in favour of api's with ->setName( $name, $langCode );

  Either way this means that there should be logic that gets default language from settings if not defined, and there
  should also be transparent handling of fallback languages based on settings as well.


Conclusion
~~~~~~~~~~
