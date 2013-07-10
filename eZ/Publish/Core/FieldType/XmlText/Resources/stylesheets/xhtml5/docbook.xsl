<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ezxhtml5="http://ez.no/namespaces/ezpublish5/xhtml5"
    exclude-result-prefixes="ezxhtml5"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>

  <xsl:template match="ezxhtml5:article">
    <article xmlns="http://docbook.org/ns/docbook" version="5.0">
      <xsl:apply-templates/>
    </article>
  </xsl:template>

  <xsl:template match="ezxhtml5:section">
    <xsl:element name="section" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:p">
    <xsl:element name="para" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:em">
    <xsl:element name="emphasis" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:strong">
    <xsl:element name="emphasis" namespace="http://docbook.org/ns/docbook">
      <xsl:attribute name="role">strong</xsl:attribute>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:h1 | ezxhtml5:h2 | ezxhtml5:h3 | ezxhtml5:h4 | ezxhtml5:h5 | ezxhtml5:h6">
    <xsl:element name="title" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:ol">
    <xsl:element name="orderedlist" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:ul">
    <xsl:element name="itemizedlist" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:ol/ezxhtml5:li | ezxhtml5:ul/ezxhtml5:li">
    <xsl:element name="listitem" namespace="http://docbook.org/ns/docbook">
      <xsl:element name="para" namespace="http://docbook.org/ns/docbook">
        <xsl:apply-templates/>
      </xsl:element>
    </xsl:element>
  </xsl:template>
</xsl:stylesheet>
