<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:docbook="http://docbook.org/ns/docbook"
    exclude-result-prefixes="docbook"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>

  <xsl:template match="docbook:article">
    <section
        xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
        xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
        xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
      <xsl:apply-templates/>
    </section>
  </xsl:template>

  <xsl:template match="docbook:section">
    <section>
      <xsl:apply-templates/>
    </section>
  </xsl:template>

  <xsl:template match="docbook:para">
    <paragraph>
      <xsl:apply-templates/>
    </paragraph>
  </xsl:template>

  <xsl:template match="docbook:emphasis">
    <xsl:choose>
      <xsl:when test="@role='strong'">
        <strong>
          <xsl:apply-templates/>
        </strong>
      </xsl:when>
      <xsl:otherwise>
        <emphasize>
          <xsl:apply-templates/>
        </emphasize>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="docbook:title">
    <heading>
      <xsl:apply-templates/>
    </heading>
  </xsl:template>

  <xsl:template match="docbook:orderedlist">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <ol>
        <xsl:apply-templates/>
      </ol>
    </paragraph>
  </xsl:template>

  <xsl:template match="docbook:itemizedlist">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <ul>
        <xsl:apply-templates/>
      </ul>
    </paragraph>
  </xsl:template>

  <xsl:template match="docbook:itemizedlist/docbook:listitem/docbook:para | docbook:orderedlist/docbook:listitem/docbook:para">
    <li>
      <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
        <xsl:apply-templates/>
      </paragraph>
    </li>
  </xsl:template>
</xsl:stylesheet>
