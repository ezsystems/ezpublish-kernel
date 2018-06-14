<?xml version="1.0" encoding="UTF-8"?>
<!-- Deprecated in version 7.2, use ezplatform-xmltext-fieldtype/lib/FieldType/XmlText/Input/Resources/stylesheets/eZXml2Docbook_core.xsl instead -->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"
    xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns="http://docbook.org/ns/docbook"
    xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
    xmlns:ezlegacytmp="http://ez.no/xmlns/ezpublish/legacytmp"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>

  <xsl:key name="ids" match="//anchor[@name]" use="@name"/>
  <xsl:key name="ids" match="*[@xhtml:id]" use="@xhtml:id"/>
  <xsl:key name="ids" match="//embed[@ezlegacytmp-embed-link-id]" use="@ezlegacytmp-embed-link-id"/>

  <xsl:template match="custom">
    <xsl:element name="eztemplateinline" namespace="http://docbook.org/ns/docbook">
      <xsl:attribute name="name">
        <xsl:value-of select="@name"/>
      </xsl:attribute>
      <xsl:if test="@custom:class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@custom:class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@custom:align">
        <xsl:attribute name="ezxhtml:align">
          <xsl:value-of select="@custom:align"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="./text()">
        <xsl:element name="ezcontent" namespace="http://docbook.org/ns/docbook">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:if>
      <xsl:if test="@*[namespace-uri() = 'http://ez.no/namespaces/ezpublish3/custom/' and not( local-name() = 'class' )]">
        <xsl:element name="ezconfig" namespace="http://docbook.org/ns/docbook">
          <xsl:for-each select="@*[namespace-uri() = 'http://ez.no/namespaces/ezpublish3/custom/' and not( local-name() = 'class' )]">
            <xsl:call-template name="addHashValue">
              <xsl:with-param name="attribute" select="current()"/>
            </xsl:call-template>
          </xsl:for-each>
        </xsl:element>
      </xsl:if>
    </xsl:element>
  </xsl:template>

  <xsl:template match="paragraph[@ez-temporary]/custom">
    <xsl:element name="eztemplate" namespace="http://docbook.org/ns/docbook">
      <xsl:attribute name="name">
        <xsl:value-of select="@name"/>
      </xsl:attribute>
      <xsl:if test="@custom:class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@custom:class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@custom:align">
        <xsl:attribute name="ezxhtml:align">
          <xsl:value-of select="@custom:align"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="./* | ./text()">
        <xsl:element name="ezcontent" namespace="http://docbook.org/ns/docbook">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:if>
      <xsl:if test="@*[namespace-uri() = 'http://ez.no/namespaces/ezpublish3/custom/' and not( local-name() = 'class' ) and not( local-name() = 'align' )]">
        <xsl:element name="ezconfig" namespace="http://docbook.org/ns/docbook">
          <xsl:for-each select="@*[namespace-uri() = 'http://ez.no/namespaces/ezpublish3/custom/' and not( local-name() = 'class' ) and not( local-name() = 'align' )]">
            <xsl:call-template name="addHashValue">
              <xsl:with-param name="attribute" select="current()"/>
            </xsl:call-template>
          </xsl:for-each>
        </xsl:element>
      </xsl:if>
    </xsl:element>
  </xsl:template>

  <xsl:template match="section">
    <xsl:choose>
      <xsl:when test="count(ancestor-or-self::section) &gt; 1">
        <!--xsl:element name="section" namespace="http://docbook.org/ns/docbook"-->
        <xsl:apply-templates/>
        <!--/xsl:element-->
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

  <xsl:template match="paragraph[@ez-temporary]">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="paragraph">
    <xsl:element name="para" namespace="http://docbook.org/ns/docbook">
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@align">
        <xsl:attribute name="ezxhtml:textalign">
          <xsl:value-of select="@align"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:variable name="lines" select="line"/>
      <xsl:choose>
        <xsl:when test="count( $lines ) &gt; 0">
          <xsl:element name="literallayout" namespace="http://docbook.org/ns/docbook">
            <xsl:attribute name="class">normal</xsl:attribute>
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
  </xsl:template>

  <xsl:template match="custom[@name='quote']">
    <xsl:element name="blockquote" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
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
      <xsl:attribute name="role">strong</xsl:attribute>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="custom[@name='underline' or @name='strike' or @name='sub' or @name='sup']">
    <xsl:choose>
      <xsl:when test="@name='underline'">
        <xsl:element name="emphasis" namespace="http://docbook.org/ns/docbook">
          <xsl:attribute name="role">underlined</xsl:attribute>
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:when test="@name='strike'">
        <xsl:element name="emphasis" namespace="http://docbook.org/ns/docbook">
          <xsl:attribute name="role">strikedthrough</xsl:attribute>
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
      <xsl:choose>
        <xsl:when test="count(key('ids', @name)) &gt; 1">
          <xsl:attribute name="xml:id">
            <xsl:value-of select="concat('duplicated_id_', @name, '_', generate-id(.))"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:otherwise>
          <xsl:attribute name="xml:id">
            <xsl:value-of select="@name"/>
          </xsl:attribute>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:element>
  </xsl:template>

  <xsl:template match="link">
    <xsl:element name="link" namespace="http://docbook.org/ns/docbook">
      <xsl:call-template name="addLinkAttributes"/>
      <xsl:if test="@xhtml:id">
        <xsl:choose>
          <xsl:when test="count(key('ids', @xhtml:id)) &gt; 1">
            <xsl:attribute name="xml:id">
              <xsl:value-of select="concat('duplicated_id_', @xhtml:id, '_', generate-id(.))"/>
            </xsl:attribute>
          </xsl:when>
          <xsl:otherwise>
            <xsl:attribute name="xml:id">
              <xsl:value-of select="@xhtml:id"/>
            </xsl:attribute>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:if>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template name="addLinkAttributes">
    <xsl:variable name="fragment">
      <xsl:if test="@anchor_name != ''">
        <xsl:value-of select="concat( '#', @anchor_name )"/>
      </xsl:if>
    </xsl:variable>
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
          Unhandled link typeccc
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
    <xsl:if test="@xhtml:title">
      <xsl:attribute name="xlink:title">
        <xsl:value-of select="@xhtml:title"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template match="header">
    <xsl:variable name="headingLevel">
      <xsl:choose>
        <xsl:when test="ancestor::table">
          <xsl:value-of select="count(ancestor::section[ancestor::table[1]]) + 1"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="count(ancestor::section)"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:element name="title" namespace="http://docbook.org/ns/docbook">
      <xsl:attribute name="ezxhtml:level">
        <xsl:choose>
          <xsl:when test="$headingLevel = 1">
            <xsl:value-of select="2"/>
          </xsl:when>
          <xsl:when test="$headingLevel &gt; 6">
            <xsl:value-of select="6"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$headingLevel"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@align">
        <xsl:attribute name="ezxhtml:textalign">
          <xsl:value-of select="@align"/>
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
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
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
      <xsl:if test="@custom:summary != ''">
        <xsl:attribute name="title">
          <xsl:value-of select="@custom:summary"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@border != 0">
        <xsl:attribute name="border">1</xsl:attribute>
        <xsl:attribute name="style">
          <xsl:choose>
            <xsl:when test="contains( @border, '%' )">
              <xsl:value-of select="concat( 'border-width:', @border, ';' )"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="concat( 'border-width:', @border, 'px;' )"/>
            </xsl:otherwise>
          </xsl:choose>
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
      <xsl:if test="@align">
        <xsl:attribute name="ezxhtml:textalign">
          <xsl:value-of select="@align"/>
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
      <xsl:if test="@align">
        <xsl:attribute name="ezxhtml:textalign">
          <xsl:value-of select="@align"/>
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

  <xsl:template match="embed[@node_id|@object_id] | embed-inline[@node_id|@object_id]">
    <xsl:variable name="embedname">
      <xsl:choose>
        <xsl:when test="local-name() = 'embed-inline'">
          <xsl:value-of select="'ezembedinline'"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="'ezembed'"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:element name="{$embedname}" namespace="http://docbook.org/ns/docbook">
      <xsl:choose>
        <xsl:when test="@node_id">
          <xsl:attribute name="xlink:href">
            <xsl:value-of select="concat( 'ezlocation://', @node_id )"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:when test="@object_id">
          <xsl:attribute name="xlink:href">
            <xsl:value-of select="concat( 'ezcontent://', @object_id )"/>
          </xsl:attribute>
        </xsl:when>
      </xsl:choose>
      <xsl:if test="@xhtml:id">
        <xsl:choose>
          <xsl:when test="count(key('ids', @xhtml:id)) &gt; 1">
            <xsl:attribute name="xml:id">
              <xsl:value-of select="concat('duplicated_id_', @xhtml:id, '_', generate-id(.))"/>
            </xsl:attribute>
          </xsl:when>
          <xsl:otherwise>
            <xsl:attribute name="xml:id">
              <xsl:value-of select="@xhtml:id"/>
            </xsl:attribute>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:if>
      <xsl:if test="@view">
        <xsl:attribute name="view">
          <xsl:value-of select="@view"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@align">
        <xsl:attribute name="ezxhtml:align">
          <xsl:value-of select="@align"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@*[starts-with( name( . ), 'ezlegacytmp-embed-link-' )]">
        <xsl:element name="ezlink" namespace="http://docbook.org/ns/docbook">
          <xsl:call-template name="addEmbedLinkAttributes"/>
        </xsl:element>
      </xsl:if>
      <xsl:if test="@size or @*[namespace-uri() = 'http://ez.no/namespaces/ezpublish3/custom/']">
        <xsl:element name="ezconfig" namespace="http://docbook.org/ns/docbook">
          <xsl:for-each select="@size | @*[namespace-uri() = 'http://ez.no/namespaces/ezpublish3/custom/']">
            <xsl:call-template name="addHashValue">
              <xsl:with-param name="attribute" select="current()"/>
            </xsl:call-template>
          </xsl:for-each>
        </xsl:element>
      </xsl:if>
    </xsl:element>
  </xsl:template>

  <xsl:template name="addEmbedLinkAttributes">
    <xsl:variable name="fragment">
      <xsl:if test="@ezlegacytmp-embed-link-anchor_name != ''">
        <xsl:value-of select="concat( '#', @ezlegacytmp-embed-link-anchor_name )"/>
      </xsl:if>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="@ezlegacytmp-embed-link-url_id">
        <xsl:attribute name="xlink:href">
          <xsl:value-of select="concat( 'ezurl://', @ezlegacytmp-embed-link-url_id, $fragment )"/>
        </xsl:attribute>
      </xsl:when>
      <xsl:when test="@ezlegacytmp-embed-link-node_id">
        <xsl:attribute name="xlink:href">
          <xsl:value-of select="concat( 'ezlocation://', @ezlegacytmp-embed-link-node_id, $fragment )"/>
        </xsl:attribute>
      </xsl:when>
      <xsl:when test="@ezlegacytmp-embed-link-object_id">
        <xsl:attribute name="xlink:href">
          <xsl:value-of select="concat( 'ezcontent://', @ezlegacytmp-embed-link-object_id, $fragment )"/>
        </xsl:attribute>
      </xsl:when>
    </xsl:choose>
    <xsl:if test="@ezlegacytmp-embed-link-url_id or @ezlegacytmp-embed-link-node_id or @ezlegacytmp-embed-link-object_id">
      <xsl:attribute name="xlink:show">
        <xsl:choose>
          <xsl:when test="@ezlegacytmp-embed-link-target and @ezlegacytmp-embed-link-target = '_blank'">
            <xsl:value-of select="'new'"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="'none'"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
    </xsl:if>
    <xsl:if test="@ezlegacytmp-embed-link-title">
      <xsl:attribute name="xlink:title">
        <xsl:value-of select="@ezlegacytmp-embed-link-title"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:if test="@ezlegacytmp-embed-link-id">
      <xsl:choose>
        <xsl:when test="count(key('ids', @ezlegacytmp-embed-link-id)) &gt; 1">
          <xsl:attribute name="xml:id">
            <xsl:value-of select="concat('duplicated_id_', @ezlegacytmp-embed-link-id, '_', generate-id(.))"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:otherwise>
          <xsl:attribute name="xml:id">
            <xsl:value-of select="@ezlegacytmp-embed-link-id"/>
          </xsl:attribute>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:if>
    <xsl:if test="@ezlegacytmp-embed-link-class">
      <xsl:attribute name="ezxhtml:class">
        <xsl:value-of select="@ezlegacytmp-embed-link-class"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template name="addHashValue">
    <xsl:param name="attribute"/>
    <xsl:element name="ezvalue" namespace="http://docbook.org/ns/docbook">
      <xsl:attribute name="key">
        <xsl:value-of select="local-name( $attribute )"/>
      </xsl:attribute>
      <xsl:value-of select="$attribute"/>
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>
