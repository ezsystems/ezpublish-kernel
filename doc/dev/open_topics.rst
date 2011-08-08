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


ezp\Persistence\Content\Type\Group->contentTypes
------------------------------------------------

Summary
~~~~~~~
Implies always eager loading of content types despite use.

Conclusion
~~~~~~~~~~


ezp\Persistence\Content->name
-----------------------------

Summary
~~~~~~~
Does not follow same format as other name/description fields that have several languages.

Conclusion
~~~~~~~~~~


VO / create struct / update struct OOP
--------------------------------------

Summary
~~~~~~~
This objects have 95% similarities and should probably extend each other for code/doc reuse.
Eg: VO (and update struct) could if possible extend createstruct and in most cases just add $id.
NOTE: If done then api's that allow create struct should be checked to make sure it does not fail if it gets a VO.

Conclusion
~~~~~~~~~~
