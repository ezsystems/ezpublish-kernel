<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:docbook="http://docbook.org/ns/docbook"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    exclude-result-prefixes="docbook xlink"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>
  <xsl:variable name="outputNamespace" select="''"/>

  <xsl:template match="docbook:article">
    <xsl:if test="not(parent::*)">
      <xsl:element name="article" namespace="{$outputNamespace}">
        <xsl:apply-templates/>
      </xsl:element>
    </xsl:if>
  </xsl:template>

  <xsl:template match="docbook:section">
    <xsl:element name="section" namespace="{$outputNamespace}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:para">
    <xsl:element name="p" namespace="{$outputNamespace}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template name="breakLine">
    <xsl:param name="text"/>
    <xsl:variable name="newLine">
      <xsl:text>&#xa;</xsl:text>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="contains( $text, $newLine )">
        <xsl:value-of select="substring-before( $text, $newLine )"/>
        <xsl:element name="br" namespace="{$outputNamespace}"/>
        <xsl:call-template name="breakLine">
          <xsl:with-param name="text" select="substring-after( $text, $newLine )"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$text"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="paragraphLiterallayout">
    <xsl:param name="nodes"/>
    <xsl:if test="$nodes">
      <xsl:choose>
        <xsl:when test="name( $nodes[1]/.. ) = 'literallayout' and $nodes[1][last()]/self::text()">
          <xsl:call-template name="breakLine">
            <xsl:with-param name="text" select="$nodes[1]"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:apply-templates select="$nodes[1]"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="count( $nodes ) &gt; 1">
        <xsl:call-template name="paragraphLiterallayout">
          <xsl:with-param name="nodes" select="$nodes[position() &gt; 1]"/>
        </xsl:call-template>
      </xsl:if>
    </xsl:if>
  </xsl:template>

  <xsl:template match="docbook:para/docbook:literallayout">
    <xsl:call-template name="paragraphLiterallayout">
      <xsl:with-param name="nodes" select="node()"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template match="docbook:emphasis">
    <xsl:choose>
      <xsl:when test="@role='strong'">
        <xsl:element name="strong" namespace="{$outputNamespace}">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:when test="@role='underlined'">
        <xsl:element name="u" namespace="{$outputNamespace}">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="em" namespace="{$outputNamespace}">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="docbook:anchor">
    <xsl:element name="a" namespace="{$outputNamespace}">
      <xsl:attribute name="id">
        <xsl:value-of select="@xml:id"/>
      </xsl:attribute>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:link[@xlink:href]">
    <xsl:element name="a" namespace="{$outputNamespace}">
      <xsl:attribute name="href">
        <xsl:value-of select="@xlink:href"/>
      </xsl:attribute>
      <xsl:if test="@xlink:show = 'new'">
        <xsl:attribute name="target">
          <xsl:value-of select="'_blank'"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xml:id">
        <xsl:attribute name="id">
          <xsl:value-of select="@xml:id"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xlink:title">
        <xsl:attribute name="title">
          <xsl:value-of select="@xlink:title"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:title">
    <xsl:variable name="level" select="count(ancestor-or-self::docbook:section) + 2"/>

    <xsl:choose>
      <xsl:when test="$level &gt; 6">
        <xsl:element name="h6" namespace="{$outputNamespace}">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="h{$level}" namespace="{$outputNamespace}">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="docbook:orderedlist">
    <xsl:element name="ol" namespace="{$outputNamespace}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:itemizedlist">
    <xsl:element name="ul" namespace="{$outputNamespace}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:orderedlist/docbook:listitem/docbook:para | docbook:itemizedlist/docbook:listitem/docbook:para">
    <xsl:element name="li" namespace="{$outputNamespace}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:table | docbook:informaltable">
    <xsl:element name="table" namespace="{$outputNamespace}">
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@width">
        <xsl:attribute name="width">
          <xsl:value-of select="@width"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@border">
        <xsl:attribute name="border">
          <xsl:value-of select="@border"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@title">
        <xsl:attribute name="title">
          <xsl:value-of select="@title"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="local-name(.) = 'table' and ./docbook:caption != ''">
        <xsl:element name="caption" namespace="{$outputNamespace}">
          <xsl:value-of select="./docbook:caption"/>
        </xsl:element>
      </xsl:if>
      <xsl:for-each select="./docbook:tr | ./docbook:tbody/docbook:tr">
        <xsl:apply-templates select="current()"/>
      </xsl:for-each>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:tr">
    <xsl:element name="tr" namespace="{$outputNamespace}">
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:th">
    <xsl:element name="th" namespace="{$outputNamespace}">
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <!--xsl:if test="@xhtml:width">
          <xsl:attribute name="width">
              <xsl:value-of select="@xhtml:width"/>
          </xsl:attribute>
      </xsl:if-->
      <xsl:if test="@valign">
        <xsl:attribute name="valign">
          <xsl:value-of select="@valign"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@colspan">
        <xsl:attribute name="colspan">
          <xsl:value-of select="@colspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@rowspan">
        <xsl:attribute name="rowspan">
          <xsl:value-of select="@rowspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@abbr">
        <xsl:attribute name="abbr">
          <xsl:value-of select="@abbr"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@scope">
        <xsl:attribute name="scope">
          <xsl:value-of select="@scope"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:td">
    <xsl:element name="td" namespace="{$outputNamespace}">
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <!--xsl:if test="@xhtml:width">
        <xsl:attribute name="width">
          <xsl:value-of select="@xhtml:width"/>
        </xsl:attribute>
      </xsl:if-->
      <xsl:if test="@valign">
        <xsl:attribute name="valign">
          <xsl:value-of select="@valign"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@colspan">
        <xsl:attribute name="colspan">
          <xsl:value-of select="@colspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@rowspan">
        <xsl:attribute name="rowspan">
          <xsl:value-of select="@rowspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>
</xsl:stylesheet>
