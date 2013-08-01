<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"
    xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns="http://docbook.org/ns/docbook"
    xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>

  <xsl:template match="section">
    <xsl:choose>
      <xsl:when test="count(ancestor-or-self::section) &gt; 1">
        <xsl:element name="section" namespace="http://docbook.org/ns/docbook">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <section xmlns="http://docbook.org/ns/docbook"
                 xmlns:xlink="http://www.w3.org/1999/xlink"
                 xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
                 xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
                 version="5.0-variant ezpublish-1.0">
          <xsl:apply-templates/>
        </section>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- TODO investigate always stripping paragraphs with temporary namespace -->
  <xsl:template match="paragraph">
    <xsl:choose>
      <xsl:when test="( table | ul | ol ) or name( .. ) = 'li'">
        <xsl:apply-templates/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="para" namespace="http://docbook.org/ns/docbook">
          <xsl:if test="@class">
            <xsl:attribute name="ezxhtml:class">
              <xsl:value-of select="@class"/>
            </xsl:attribute>
          </xsl:if>
          <xsl:variable name="lines" select="line"/>
          <xsl:choose>
            <xsl:when test="count( $lines ) &gt; 0">
              <xsl:element name="literallayout" namespace="http://docbook.org/ns/docbook">
                <xsl:attribute name="class">
                  <xsl:value-of select="'normal'"/>
                </xsl:attribute>
                <xsl:for-each select="$lines">
                  <xsl:apply-templates/>
                  <xsl:if test='position() != last()'>
                    <xsl:text>&#xa;</xsl:text>
                  </xsl:if>
                </xsl:for-each>
              </xsl:element>
            </xsl:when>
            <xsl:otherwise>
              <xsl:apply-templates/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="emphasize">
    <xsl:element name="emphasis" namespace="http://docbook.org/ns/docbook">
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="strong">
    <xsl:element name="emphasis" namespace="http://docbook.org/ns/docbook">
      <xsl:if test="@class">
        <xsl:attribute name="role">strong</xsl:attribute>
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="custom[@name='underline' or @name='sub' or @name='sup']">
    <xsl:choose>
      <xsl:when test="@name='underline'">
        <xsl:element name="emphasis" namespace="http://docbook.org/ns/docbook">
          <xsl:attribute name="role">underlined</xsl:attribute>
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:when test="@name='sub'">
        <xsl:element name="subscript" namespace="http://docbook.org/ns/docbook">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:when test="@name='sup'">
        <xsl:element name="superscript" namespace="http://docbook.org/ns/docbook">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="anchor">
    <xsl:element name="anchor" namespace="http://docbook.org/ns/docbook">
      <xsl:attribute name="xml:id">
        <xsl:value-of select="@name"/>
      </xsl:attribute>
    </xsl:element>
  </xsl:template>

  <xsl:template match="link">
    <xsl:variable name="fragment">
      <xsl:if test="@anchor_name != ''">
        <xsl:value-of select="concat( '#', @anchor_name )"/>
      </xsl:if>
    </xsl:variable>
    <xsl:element name="link" namespace="http://docbook.org/ns/docbook">
      <xsl:choose>
        <xsl:when test="@url_id">
          <xsl:attribute name="xlink:href">
            <xsl:value-of select="concat( 'ezurl://', @url_id, $fragment )"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:when test="@node_id">
          <xsl:attribute name="xlink:href">
            <xsl:value-of select="concat( 'ezlocation://', @node_id, $fragment )"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:when test="@object_id">
          <xsl:attribute name="xlink:href">
            <xsl:value-of select="concat( 'ezcontent://', @object_id, $fragment )"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:when test="@anchor_name">
          <xsl:attribute name="xlink:href">
            <xsl:value-of select="concat( '#', @anchor_name )"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:otherwise>
          <xsl:message terminate="yes">
            Unhandled link type
          </xsl:message>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:attribute name="xlink:show">
        <xsl:choose>
          <xsl:when test="@target and @target = '_blank'">
            <xsl:value-of select="'new'"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="'none'"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:if test="@xhtml:id">
        <xsl:attribute name="xml:id">
          <xsl:value-of select="@xhtml:id"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xhtml:title">
        <xsl:attribute name="xlink:title">
          <xsl:value-of select="@xhtml:title"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="header">
    <xsl:element name="title" namespace="http://docbook.org/ns/docbook">
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ul">
    <xsl:element name="itemizedlist" namespace="http://docbook.org/ns/docbook">
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ol">
    <xsl:element name="orderedlist" namespace="http://docbook.org/ns/docbook">
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ul/li | ol/li">
    <xsl:element name="listitem" namespace="http://docbook.org/ns/docbook">
      <xsl:element name="para" namespace="http://docbook.org/ns/docbook">
        <xsl:apply-templates/>
      </xsl:element>
    </xsl:element>
  </xsl:template>

  <xsl:template match="table">
    <xsl:variable name="tableElement">
      <xsl:choose>
        <xsl:when test="@custom:caption != ''">
          <xsl:value-of select="'table'"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="'informaltable'"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:element name="{$tableElement}" namespace="http://docbook.org/ns/docbook">
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
      <xsl:if test="@border != 0">
        <xsl:attribute name="border">
          <xsl:value-of select="@border"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@custom:summary != ''">
        <xsl:attribute name="title">
          <xsl:value-of select="@custom:summary"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="$tableElement = 'table'">
        <xsl:element name="caption" namespace="http://docbook.org/ns/docbook">
          <xsl:value-of select="@custom:caption"/>
        </xsl:element>
      </xsl:if>
      <xsl:element name="tbody" namespace="http://docbook.org/ns/docbook">
        <xsl:for-each select="./tr">
          <xsl:apply-templates select="current()"/>
        </xsl:for-each>
      </xsl:element>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tr">
    <xsl:element name="tr" namespace="http://docbook.org/ns/docbook">
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="th">
    <xsl:element name="th" namespace="http://docbook.org/ns/docbook">
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xhtml:width">
        <xsl:attribute name="ezxhtml:width">
          <xsl:value-of select="@xhtml:width"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@custom:valign">
        <xsl:attribute name="valign">
          <xsl:value-of select="@custom:valign"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xhtml:colspan">
        <xsl:attribute name="colspan">
          <xsl:value-of select="@xhtml:colspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xhtml:rowspan">
        <xsl:attribute name="rowspan">
          <xsl:value-of select="@xhtml:rowspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@custom:abbr">
        <xsl:attribute name="abbr">
          <xsl:value-of select="@custom:abbr"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@custom:scope">
        <xsl:attribute name="scope">
          <xsl:value-of select="@custom:scope"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="td">
    <xsl:element name="td" namespace="http://docbook.org/ns/docbook">
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xhtml:width">
        <xsl:attribute name="ezxhtml:width">
          <xsl:value-of select="@xhtml:width"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@custom:valign">
        <xsl:attribute name="valign">
          <xsl:value-of select="@custom:valign"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xhtml:colspan">
        <xsl:attribute name="colspan">
          <xsl:value-of select="@xhtml:colspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xhtml:rowspan">
        <xsl:attribute name="rowspan">
          <xsl:value-of select="@xhtml:rowspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <!-- All custom elements not otherwise matched are copied under 'ezcustom' namespace, together with their attributes -->
  <xsl:template match="custom">
    <xsl:element name="ezcustom:{local-name()}">
      <xsl:for-each select="@*">
        <xsl:attribute name="ezcustom:{local-name()}">
          <xsl:value-of select="current()"/>
        </xsl:attribute>
      </xsl:for-each>
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>
