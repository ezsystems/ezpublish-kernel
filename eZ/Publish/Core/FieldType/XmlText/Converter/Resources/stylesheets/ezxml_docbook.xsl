<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>
  <xsl:strip-space elements="*"/>

  <xsl:template match="section">
    <xsl:choose>
      <xsl:when test="count(ancestor-or-self::section) &gt; 1">
        <xsl:element name="section" namespace="http://docbook.org/ns/docbook">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <article xmlns="http://docbook.org/ns/docbook" version="5.0">
          <xsl:apply-templates/>
        </article>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="paragraph">
    <xsl:element name="para" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="emphasize">
    <xsl:element name="emphasis" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="strong">
    <xsl:element name="emphasis" namespace="http://docbook.org/ns/docbook">
      <xsl:attribute name="role">strong</xsl:attribute>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="heading">
    <xsl:element name="title" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>
</xsl:stylesheet>
