<?xml version="1.0" encoding="utf-8"?>
<s:schema xmlns:s="http://purl.oclc.org/dsdl/schematron"
          xmlns:ezxhtml5edit="http://ez.no/namespaces/ezpublish5/xhtml5/edit">
  <s:ns prefix="ezxhtml5edit" uri="http://ez.no/namespaces/ezpublish5/xhtml5/edit"/>
  <s:pattern name="Element exclusion">
    <s:rule context="ezxhtml5edit:a">
      <s:assert test="not(.//ezxhtml5edit:ezlink)">ezlink must not occur in the descendants of a</s:assert>
    </s:rule>
  </s:pattern>
</s:schema>
