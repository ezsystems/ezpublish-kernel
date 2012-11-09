<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" exclude-result-prefixes="xhtml custom image">
<xsl:output method="html" indent="yes" encoding="UTF-8" />

<xsl:template match="/ | section">
<xsl:apply-templates/>
</xsl:template>

<xsl:template match="header">
<xsl:variable name="level" select="count(ancestor-or-self::section)"/>
<xsl:variable name="name">
<xsl:number count="section" level="multiple"/>
</xsl:variable>

<a name="eztoc{translate($name, '.', '_')}" id="eztoc{translate($name, '.', '_')}"/>
<xsl:element name="h{$level}">
<xsl:apply-templates/>
</xsl:element>
</xsl:template>

<xsl:template match="paragraph">
    <xsl:choose>
        <xsl:when test="( ul | ol | table ) or (name(..)='li')">
            <xsl:apply-templates/>
        </xsl:when>
        <xsl:otherwise><p><xsl:apply-templates/></p></xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template match="line">
    <xsl:if test="count(preceding-sibling::*) &gt; 0">
        <br/>
    </xsl:if>
    <xsl:apply-templates/>
</xsl:template>

<xsl:template match="table">
<xsl:element name="table" use-attribute-sets="table"><xsl:apply-templates/></xsl:element>
</xsl:template>

<xsl:attribute-set name="table">
  <xsl:attribute name="class"><xsl:value-of select="@class"/></xsl:attribute>
  <xsl:attribute name="border"><xsl:value-of select="@border"/></xsl:attribute>
  <xsl:attribute name="cellpadding">
      <xsl:choose>
          <xsl:when test="@cellpadding">
              <xsl:value-of select="@cellpadding"/>
          </xsl:when>
          <xsl:otherwise>2</xsl:otherwise>
      </xsl:choose>
  </xsl:attribute>
  <xsl:attribute name="cellspacing">
      <xsl:choose>
          <xsl:when test="@cellspacing">
              <xsl:value-of select="@cellspacing"/>
          </xsl:when>
          <xsl:otherwise>0</xsl:otherwise>
      </xsl:choose>
  </xsl:attribute>
  <xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute>
  <xsl:attribute name="style">width: <xsl:value-of select="@width"/>;</xsl:attribute>
  <xsl:attribute name="summary"><xsl:value-of select="@custom:summary"/></xsl:attribute>
</xsl:attribute-set>

<xsl:template match="tr">
<tr><xsl:copy-of select="@*"/><xsl:apply-templates/></tr>
</xsl:template>

<xsl:template match="td | th">
<xsl:copy>
<xsl:choose>
    <xsl:when test="@valign">
        <xsl:attribute name="valign"><xsl:value-of select="@valign"/></xsl:attribute>
        <xsl:attribute name="style">vertical-align: <xsl:value-of select="@valign"/>;</xsl:attribute>
    </xsl:when>
    <xsl:otherwise>
        <xsl:attribute name="valign">top</xsl:attribute>
        <xsl:attribute name="style">vertical-align: top;</xsl:attribute>
    </xsl:otherwise>
</xsl:choose>
<xsl:if test="@xhtml:colspan">
    <xsl:attribute name="colspan"><xsl:value-of select="@xhtml:colspan"/></xsl:attribute>
</xsl:if>
<xsl:if test="@xhtml:rowspan">
    <xsl:attribute name="rowspan"><xsl:value-of select="@xhtml:rowspan"/></xsl:attribute>
</xsl:if>
<xsl:if test="@xhtml:width">
    <xsl:attribute name="width"><xsl:value-of select="@xhtml:width"/></xsl:attribute>
</xsl:if>
<xsl:apply-templates/>
</xsl:copy>
</xsl:template>

<xsl:template match="strong">
<b><xsl:copy-of select="@*"/><xsl:apply-templates/></b>
</xsl:template>

<xsl:template match="emphasize">
<i><xsl:copy-of select="@*"/><xsl:apply-templates/></i>
</xsl:template>

<xsl:template match="ol | ul | li">
<xsl:copy><xsl:apply-templates/></xsl:copy>
</xsl:template>

<xsl:template match="link">
<a><xsl:attribute name="href"><xsl:value-of select="@url"/></xsl:attribute><xsl:attribute name="target"><xsl:choose><xsl:when test="@target"><xsl:value-of select="@target"/></xsl:when><xsl:otherwise>_self</xsl:otherwise></xsl:choose></xsl:attribute><xsl:if test="@title"><xsl:attribute name="title"><xsl:value-of select="@title"/></xsl:attribute></xsl:if><xsl:apply-templates/></a>
</xsl:template>

<xsl:template match="embed">
<xsl:value-of select="text()" disable-output-escaping="yes"/>
</xsl:template>

<xsl:template match="literal">
<pre><xsl:copy-of select="@*"/><xsl:apply-templates/></pre>
</xsl:template>

<!-- copy unknown elements as-is -->
<xsl:template match="@* | node()">
<xsl:copy>
<xsl:apply-templates select="@* | node()" />
</xsl:copy>
</xsl:template>

</xsl:stylesheet>
