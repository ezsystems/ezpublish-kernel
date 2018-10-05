<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ezxhtml5="http://ez.no/namespaces/ezpublish5/xhtml5/edit"
    xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns="http://docbook.org/ns/docbook"
    exclude-result-prefixes="ezxhtml5"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>

  <xsl:template match="/ezxhtml5:section">
    <section xmlns="http://docbook.org/ns/docbook"
             xmlns:xlink="http://www.w3.org/1999/xlink"
             xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
             xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
             version="5.0-variant ezpublish-1.0">
      <xsl:apply-templates/>
    </section>
  </xsl:template>

  <xsl:template match="ezxhtml5:section">
    <section>
      <xsl:apply-templates/>
    </section>
  </xsl:template>

  <xsl:template name="breakline">
    <xsl:param name="node"/>
    <xsl:choose>
      <xsl:when test="descendant::ezxhtml5:br">
        <xsl:for-each select="$node">
          <xsl:choose>
            <xsl:when test="local-name( current() ) = 'br'">
              <xsl:text>&#xA;</xsl:text>
            </xsl:when>
            <xsl:otherwise>
              <xsl:apply-templates select="current()"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="ezxhtml5:p">
    <para>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="contains( @style, 'text-align:' )">
        <xsl:variable name="textAlign">
          <xsl:call-template name="extractStyleValue">
            <xsl:with-param name="style" select="@style"/>
            <xsl:with-param name="property" select="'text-align'"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:if test="$textAlign != ''">
          <xsl:attribute name="ezxhtml:textalign">
            <xsl:value-of select="$textAlign"/>
          </xsl:attribute>
        </xsl:if>
      </xsl:if>
      <xsl:choose>
        <xsl:when test="descendant::ezxhtml5:br">
          <literallayout class="normal">
            <xsl:for-each select="node()">
              <xsl:choose>
                <xsl:when test="local-name( current() ) = 'br'">
                  <xsl:text>&#xA;</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:apply-templates select="current()"/>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:for-each>
          </literallayout>
        </xsl:when>
        <xsl:otherwise>
          <xsl:apply-templates/>
        </xsl:otherwise>
      </xsl:choose>
    </para>
  </xsl:template>

  <xsl:template match="ezxhtml5:pre">
    <xsl:element name="programlisting">
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@data-language">
        <xsl:attribute name="language">
          <xsl:value-of select="@data-language"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
      <xsl:value-of disable-output-escaping="yes" select="./text()"/>
      <xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:blockquote">
    <blockquote>
      <xsl:apply-templates/>
    </blockquote>
  </xsl:template>

  <xsl:template match="ezxhtml5:em">
    <emphasis>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:call-template name="breakline">
        <xsl:with-param name="node" select="node()"/>
      </xsl:call-template>
    </emphasis>
  </xsl:template>

  <xsl:template match="ezxhtml5:strong">
    <emphasis>
      <xsl:attribute name="role">strong</xsl:attribute>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:call-template name="breakline">
        <xsl:with-param name="node" select="node()"/>
      </xsl:call-template>
    </emphasis>
  </xsl:template>

  <xsl:template match="ezxhtml5:u">
    <emphasis>
      <xsl:attribute name="role">underlined</xsl:attribute>
      <xsl:call-template name="breakline">
        <xsl:with-param name="node" select="node()"/>
      </xsl:call-template>
    </emphasis>
  </xsl:template>

  <xsl:template match="ezxhtml5:s">
    <emphasis>
      <xsl:attribute name="role">strikedthrough</xsl:attribute>
      <xsl:call-template name="breakline">
        <xsl:with-param name="node" select="node()"/>
      </xsl:call-template>
    </emphasis>
  </xsl:template>

  <xsl:template match="ezxhtml5:del">
    <emphasis>
      <xsl:attribute name="role">strikedthrough</xsl:attribute>
      <xsl:attribute name="revisionflag">deleted</xsl:attribute>
      <xsl:call-template name="breakline">
        <xsl:with-param name="node" select="node()"/>
      </xsl:call-template>
    </emphasis>
  </xsl:template>

  <xsl:template match="ezxhtml5:sub">
    <subscript>
      <xsl:apply-templates/>
    </subscript>
  </xsl:template>

  <xsl:template match="ezxhtml5:sup">
    <superscript>
      <xsl:apply-templates/>
    </superscript>
  </xsl:template>

  <xsl:template name="link.href">
    <link>
      <xsl:attribute name="xlink:href">
        <xsl:value-of select="@href"/>
      </xsl:attribute>
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
      <xsl:if test="@id">
        <xsl:attribute name="xml:id">
          <xsl:value-of select="@id"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@title">
        <xsl:attribute name="xlink:title">
          <xsl:value-of select="@title"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </link>
  </xsl:template>

  <xsl:template name="link.anchor">
    <anchor>
      <xsl:attribute name="xml:id">
        <xsl:value-of select="@id"/>
      </xsl:attribute>
    </anchor>
  </xsl:template>

  <xsl:template match="ezxhtml5:a">
    <xsl:choose>
      <xsl:when test="@href">
        <xsl:call-template name="link.href"/>
      </xsl:when>
      <xsl:when test="@id">
        <xsl:call-template name="link.anchor"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:message terminate="yes">
          Unhandled link type
        </xsl:message>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="ezxhtml5:h1 | ezxhtml5:h2 | ezxhtml5:h3 | ezxhtml5:h4 | ezxhtml5:h5 | ezxhtml5:h6">
    <title>
      <xsl:attribute name="ezxhtml:level">
        <xsl:value-of select="substring-after( local-name(), 'h' )"/>
      </xsl:attribute>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="contains( @style, 'text-align:' )">
        <xsl:variable name="textAlign">
          <xsl:call-template name="extractStyleValue">
            <xsl:with-param name="style" select="@style"/>
            <xsl:with-param name="property" select="'text-align'"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:if test="$textAlign != ''">
          <xsl:attribute name="ezxhtml:textalign">
            <xsl:value-of select="$textAlign"/>
          </xsl:attribute>
        </xsl:if>
      </xsl:if>
      <xsl:apply-templates/>
    </title>
  </xsl:template>

  <xsl:template match="ezxhtml5:ol">
    <orderedlist>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </orderedlist>
  </xsl:template>

  <xsl:template match="ezxhtml5:ul">
    <itemizedlist>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </itemizedlist>
  </xsl:template>

  <xsl:template match="ezxhtml5:ol/ezxhtml5:li | ezxhtml5:ul/ezxhtml5:li">
    <listitem>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <para>
        <xsl:apply-templates/>
      </para>
    </listitem>
  </xsl:template>

  <xsl:template match="ezxhtml5:table">
    <xsl:variable name="tablename">
      <xsl:choose>
        <xsl:when test="./ezxhtml5:caption">
          <xsl:value-of select="'table'"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="'informaltable'"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:element name="{$tablename}" namespace="http://docbook.org/ns/docbook">
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="contains( @style, 'width:' )">
        <xsl:variable name="width">
          <xsl:call-template name="extractStyleValue">
            <xsl:with-param name="style" select="@style"/>
            <xsl:with-param name="property" select="'width'"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:if test="$width != ''">
          <xsl:attribute name="width">
            <xsl:choose>
              <xsl:when test="substring( $width, string-length( $width ) - 1 ) = 'px'">
                <xsl:value-of select="substring-before( $width, 'px' )"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="$width"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
        </xsl:if>
      </xsl:if>
      <xsl:if test="@title">
        <xsl:attribute name="title">
          <xsl:value-of select="@title"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@border != ''">
        <xsl:attribute name="border">1</xsl:attribute>
      </xsl:if>
      <xsl:if test="contains( @style, 'border-width:' )">
        <xsl:variable name="borderWidth">
          <xsl:call-template name="extractStyleValue">
            <xsl:with-param name="style" select="@style"/>
            <xsl:with-param name="property" select="'border-width'"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:if test="$borderWidth != ''">
          <xsl:attribute name="style">
            <xsl:value-of select="concat( 'border-width:', $borderWidth, ';' )"/>
          </xsl:attribute>
        </xsl:if>
      </xsl:if>
      <xsl:if test="$tablename = 'table'">
        <caption>
          <xsl:value-of select="./ezxhtml5:caption"/>
        </caption>
      </xsl:if>
      <xsl:if test="./ezxhtml5:thead">
        <thead>
          <xsl:for-each select="./ezxhtml5:thead/ezxhtml5:tr">
            <xsl:apply-templates select="current()"/>
          </xsl:for-each>
        </thead>
      </xsl:if>
      <tbody>
        <xsl:for-each select="./ezxhtml5:tr | ./ezxhtml5:tbody/ezxhtml5:tr">
          <xsl:apply-templates select="current()"/>
        </xsl:for-each>
      </tbody>
      <xsl:if test="./ezxhtml5:tfoot">
        <tfoot>
          <xsl:for-each select="./ezxhtml5:tfoot/ezxhtml5:tr">
            <xsl:apply-templates select="current()"/>
          </xsl:for-each>
        </tfoot>
      </xsl:if>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:tr">
    <tr>
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </tr>
  </xsl:template>

  <xsl:template match="ezxhtml5:th">
    <th>
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="contains( @style, 'width' )">
        <xsl:variable name="width">
          <xsl:call-template name="extractStyleValue">
            <xsl:with-param name="style" select="@style"/>
            <xsl:with-param name="property" select="'width'"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:if test="$width != ''">
          <xsl:attribute name="ezxhtml:width">
            <xsl:choose>
              <xsl:when test="substring( $width, string-length( $width ) - 1 ) = 'px'">
                <xsl:value-of select="substring-before( $width, 'px' )"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="$width"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
        </xsl:if>
      </xsl:if>
      <xsl:if test="contains( @style, 'vertical-align' )">
        <xsl:variable name="verticalAlign">
          <xsl:call-template name="extractStyleValue">
            <xsl:with-param name="style" select="@style"/>
            <xsl:with-param name="property" select="'vertical-align'"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:if test="$verticalAlign != ''">
          <xsl:attribute name="valign">
            <xsl:value-of select="$verticalAlign"/>
          </xsl:attribute>
        </xsl:if>
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
      <xsl:call-template name="breakline">
       <xsl:with-param name="node" select="node()"/>
      </xsl:call-template>
    </th>
  </xsl:template>

  <xsl:template match="ezxhtml5:td">
    <td>
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="contains( @style, 'width:' )">
        <xsl:variable name="width">
          <xsl:call-template name="extractStyleValue">
            <xsl:with-param name="style" select="@style"/>
            <xsl:with-param name="property" select="'width'"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:if test="$width != ''">
          <xsl:attribute name="ezxhtml:width">
            <xsl:choose>
              <xsl:when test="substring( $width, string-length( $width ) - 1 ) = 'px'">
                <xsl:value-of select="substring-before( $width, 'px' )"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="$width"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
        </xsl:if>
      </xsl:if>
      <xsl:if test="contains( @style, 'vertical-align' )">
        <xsl:variable name="verticalAlign">
          <xsl:call-template name="extractStyleValue">
            <xsl:with-param name="style" select="@style"/>
            <xsl:with-param name="property" select="'vertical-align'"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:if test="$verticalAlign != ''">
          <xsl:attribute name="valign">
            <xsl:value-of select="$verticalAlign"/>
          </xsl:attribute>
        </xsl:if>
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
      <xsl:call-template name="breakline">
        <xsl:with-param name="node" select="node()"/>
      </xsl:call-template>
    </td>
  </xsl:template>

  <xsl:template match="ezxhtml5:div[@data-ezelement='ezembed']">
    <xsl:element name="ezembed" namespace="http://docbook.org/ns/docbook">
      <xsl:call-template name="addCommonEmbedAttributes"/>
      <xsl:apply-templates select="node()[not(self::text())]"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:span[@data-ezelement='ezembedinline']">
    <xsl:element name="ezembedinline" namespace="http://docbook.org/ns/docbook">
      <xsl:call-template name="addCommonEmbedAttributes"/>
      <xsl:apply-templates select="node()[not(self::text())]"/>
    </xsl:element>
  </xsl:template>

  <xsl:template name="addCommonEmbedAttributes">
    <xsl:if test="@id">
      <xsl:attribute name="xml:id">
        <xsl:value-of select="@id"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:if test="@data-href">
      <xsl:attribute name="xlink:href">
        <xsl:value-of select="@data-href"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:if test="@data-ezview">
      <xsl:attribute name="view">
        <xsl:value-of select="@data-ezview"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:if test="@class">
      <xsl:attribute name="ezxhtml:class">
        <xsl:value-of select="@class"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:if test="@data-ezalign">
      <xsl:attribute name="ezxhtml:align">
        <xsl:value-of select="@data-ezalign"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template match="ezxhtml5:div[@data-ezelement='ezembed']/ezxhtml5:link[@data-ezelement='ezlink'] | ezxhtml5:span[@data-ezelement='ezembedinline']/ezxhtml5:link[@data-ezelement='ezlink']">
    <xsl:element name="ezlink" namespace="http://docbook.org/ns/docbook">
      <xsl:attribute name="xlink:href">
        <xsl:value-of select="@href"/>
      </xsl:attribute>
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
      <xsl:if test="@title">
        <xsl:attribute name="xlink:title">
          <xsl:value-of select="@title"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@id">
        <xsl:attribute name="xml:id">
          <xsl:value-of select="@id"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:span[@data-ezelement='ezconfig']">
    <xsl:element name="ezconfig" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:span[@data-ezelement='ezvalue']">
    <xsl:element name="ezvalue" namespace="http://docbook.org/ns/docbook">
      <xsl:attribute name="key">
        <xsl:value-of select="@data-ezvalue-key"/>
      </xsl:attribute>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:div[@data-ezelement='eztemplate'] | ezxhtml5:span[@data-ezelement='eztemplateinline']">
    <xsl:element name="{@data-ezelement}" namespace="http://docbook.org/ns/docbook">
      <xsl:if test="@data-ezname">
        <xsl:attribute name="name">
          <xsl:value-of select="@data-ezname"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@data-eztype">
        <xsl:attribute name="type">
          <xsl:value-of select="@data-eztype"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@class">
        <xsl:attribute name="ezxhtml:class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@data-ezalign">
        <xsl:attribute name="ezxhtml:align">
          <xsl:value-of select="@data-ezalign"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:choose>
        <!-- Nest content of Style tag in ezcontent -->
        <xsl:when test="@data-eztype='style'">
          <xsl:element name="ezcontent" namespace="http://docbook.org/ns/docbook">
            <xsl:apply-templates/>
          </xsl:element>
        </xsl:when>
        <!-- For other types of tags behave as usual (ezcontent should be defined explicitly) -->
        <xsl:otherwise>
          <xsl:apply-templates/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:element>
  </xsl:template>

  <!-- handle explicitly defined eztemplate > ezcontent tag -->
  <xsl:template match="ezxhtml5:div[@data-ezelement='eztemplate']/ezxhtml5:div[@data-ezelement='ezcontent'] | ezxhtml5:span[@data-ezelement='eztemplateinline']/ezxhtml5:span[@data-ezelement='ezcontent']">
    <xsl:element name="ezcontent" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template name="extractStyleValue">
    <xsl:param name="style"/>
    <xsl:param name="property"/>
    <xsl:value-of select="translate( substring-before( substring-after( concat( substring-after( $style, $property ), ';' ), ':' ), ';' ), ' ', '' )"/>
  </xsl:template>

</xsl:stylesheet>
