<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:docbook="http://docbook.org/ns/docbook"
    exclude-result-prefixes="docbook"
    version="1.0">
  <xsl:output method="html" indent="yes" encoding="UTF-8"/>

  <xsl:template match="docbook:article">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="docbook:section">
    <section>
      <xsl:apply-templates/>
    </section>
  </xsl:template>

  <xsl:template match="docbook:para">
    <p>
      <xsl:apply-templates/>
    </p>
  </xsl:template>

  <xsl:template match="docbook:emphasis">
    <xsl:choose>
      <xsl:when test="@role='strong'">
        <strong>
          <xsl:apply-templates/>
        </strong>
      </xsl:when>
      <xsl:otherwise>
        <em>
          <xsl:apply-templates/>
        </em>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="docbook:title">
    <xsl:variable name="level" select="count(ancestor-or-self::docbook:section) + 2"/>

    <xsl:choose>
      <xsl:when test="$level &gt; 6">
        <xsl:element name="h6">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="h{$level}">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>
