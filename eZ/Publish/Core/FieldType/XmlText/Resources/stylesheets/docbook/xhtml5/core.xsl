<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:docbook="http://docbook.org/ns/docbook"
    exclude-result-prefixes="docbook"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>

  <xsl:template match="docbook:article[..]">
    <xsl:element name="article" namespace="http://ez.no/namespaces/ezpublish5/xhtml5">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:section">
    <xsl:element name="section" namespace="http://ez.no/namespaces/ezpublish5/xhtml5">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:para">
    <xsl:element name="p" namespace="http://ez.no/namespaces/ezpublish5/xhtml5">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:emphasis">
    <xsl:choose>
      <xsl:when test="@role='strong'">
        <xsl:element name="strong" namespace="http://ez.no/namespaces/ezpublish5/xhtml5">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="em" namespace="http://ez.no/namespaces/ezpublish5/xhtml5">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="docbook:title">
    <xsl:variable name="level" select="count(ancestor-or-self::docbook:section) + 2"/>

    <xsl:choose>
      <xsl:when test="$level &gt; 6">
        <xsl:element name="h6" namespace="http://ez.no/namespaces/ezpublish5/xhtml5">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="h{$level}" namespace="http://ez.no/namespaces/ezpublish5/xhtml5">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="docbook:orderedlist">
    <xsl:element name="ol" namespace="http://ez.no/namespaces/ezpublish5/xhtml5">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:itemizedlist">
    <xsl:element name="ul" namespace="http://ez.no/namespaces/ezpublish5/xhtml5">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:orderedlist/docbook:listitem/docbook:para | docbook:itemizedlist/docbook:listitem/docbook:para">
    <xsl:element name="li" namespace="http://ez.no/namespaces/ezpublish5/xhtml5">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>
</xsl:stylesheet>
