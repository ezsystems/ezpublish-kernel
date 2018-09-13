<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:docbook="http://docbook.org/ns/docbook"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
    exclude-result-prefixes="docbook xlink ezxhtml ezcustom"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>
  <xsl:variable name="outputNamespace" select="''"/>

  <xsl:template match="docbook:section">
    <xsl:if test="not(parent::*)">
      <xsl:element name="section" namespace="{$outputNamespace}">
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
      <xsl:if test="@ezxhtml:class">
        <xsl:attribute name="class">
          <xsl:value-of select="@ezxhtml:class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@ezxhtml:textalign">
        <xsl:attribute name="style">
          <xsl:value-of select="concat( 'text-align:', @ezxhtml:textalign, ';' )"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:blockquote">
    <xsl:element name="blockquote" namespace="{$outputNamespace}">
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

  <xsl:template match="docbook:emphasis/text()">
    <xsl:choose>
      <xsl:when test="ancestor::*[local-name() = 'literallayout']">
        <xsl:call-template name="breakLine">
          <xsl:with-param name="text" select="."/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="."/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="docbook:emphasis">
    <xsl:choose>
      <xsl:when test="@role='strong'">
        <xsl:element name="strong" namespace="{$outputNamespace}">
          <xsl:if test="@ezxhtml:class">
            <xsl:attribute name="class">
              <xsl:value-of select="@ezxhtml:class"/>
            </xsl:attribute>
          </xsl:if>
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:when test="@role='underlined'">
        <xsl:element name="u" namespace="{$outputNamespace}">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:when test="@role='strikedthrough'">
        <xsl:choose>
          <xsl:when test="@revisionflag='deleted'">
            <xsl:element name="del" namespace="{$outputNamespace}">
              <xsl:apply-templates/>
            </xsl:element>
          </xsl:when>
          <xsl:otherwise>
            <xsl:element name="s" namespace="{$outputNamespace}">
              <xsl:apply-templates/>
            </xsl:element>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="em" namespace="{$outputNamespace}">
          <xsl:if test="@ezxhtml:class">
            <xsl:attribute name="class">
              <xsl:value-of select="@ezxhtml:class"/>
            </xsl:attribute>
          </xsl:if>
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="docbook:subscript">
    <xsl:element name="sub" namespace="{$outputNamespace}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:superscript">
    <xsl:element name="sup" namespace="{$outputNamespace}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:anchor">
    <xsl:element name="a" namespace="{$outputNamespace}">
      <xsl:attribute name="id">
        <xsl:value-of select="@xml:id"/>
      </xsl:attribute>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:link[@xlink:href]">
    <xsl:choose>
      <xsl:when test="@xlink:href != '#'">
        <xsl:element name="a" namespace="{$outputNamespace}">
          <xsl:attribute name="href">
            <xsl:value-of select="@xlink:href"/>
          </xsl:attribute>
          <xsl:if test="@xlink:show = 'new'">
            <xsl:attribute name="target">_blank</xsl:attribute>
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
          <xsl:if test="@ezxhtml:class">
            <xsl:attribute name="class">
              <xsl:value-of select="@ezxhtml:class"/>
            </xsl:attribute>
          </xsl:if>
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="docbook:title">
    <xsl:variable name="headingLevel">
      <xsl:choose>
        <xsl:when test="@ezxhtml:level">
          <xsl:value-of select="@ezxhtml:level"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="count( ancestor-or-self::docbook:section )"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="headingTag">
      <xsl:choose>
        <xsl:when test="$headingLevel &gt; 6">
          <xsl:value-of select="'h6'"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="concat( 'h', $headingLevel )"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:element name="{$headingTag}" namespace="{$outputNamespace}">
      <xsl:if test="@ezxhtml:class">
        <xsl:attribute name="class">
          <xsl:value-of select="@ezxhtml:class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@ezxhtml:textalign">
        <xsl:attribute name="style">
          <xsl:value-of select="concat( 'text-align:', @ezxhtml:textalign, ';' )"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:orderedlist">
    <xsl:element name="ol" namespace="{$outputNamespace}">
      <xsl:if test="@ezxhtml:class">
        <xsl:attribute name="class">
          <xsl:value-of select="@ezxhtml:class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:itemizedlist">
    <xsl:element name="ul" namespace="{$outputNamespace}">
      <xsl:if test="@ezxhtml:class">
        <xsl:attribute name="class">
          <xsl:value-of select="@ezxhtml:class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:orderedlist/docbook:listitem/docbook:para | docbook:itemizedlist/docbook:listitem/docbook:para">
    <xsl:element name="li" namespace="{$outputNamespace}">
      <xsl:if test="../@ezxhtml:class">
        <xsl:attribute name="class">
          <xsl:value-of select="../@ezxhtml:class"/>
        </xsl:attribute>
      </xsl:if>
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
      <xsl:variable name="inlineStyleWidth">
        <xsl:choose>
          <xsl:when test="@width != ''">
            <xsl:choose>
              <xsl:when test="substring( @width, string-length( @width ) ) = '%'">
                <xsl:value-of select="concat( 'width:', @width, ';' )"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="concat( 'width:', @width, 'px;' )"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="''"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:variable name="inlineStyleBorder">
        <xsl:choose>
          <xsl:when test="contains( @style, 'border-width:' )">
            <xsl:variable name="borderWidth">
              <xsl:call-template name="extractStyleValue">
                <xsl:with-param name="style" select="@style"/>
                <xsl:with-param name="property" select="'border-width'"/>
              </xsl:call-template>
            </xsl:variable>
            <xsl:if test="$borderWidth != ''">
              <xsl:value-of select="concat( 'border-width:', $borderWidth, ';' )"/>
            </xsl:if>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="''"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:if test="@border != ''">
        <xsl:attribute name="border">1</xsl:attribute>
      </xsl:if>
      <xsl:if test="@title">
        <xsl:attribute name="title">
          <xsl:value-of select="@title"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="$inlineStyleWidth != '' or $inlineStyleBorder != ''">
        <xsl:attribute name="style">
          <xsl:value-of select="concat( $inlineStyleWidth, $inlineStyleBorder )"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="local-name(.) = 'table' and ./docbook:caption != ''">
        <xsl:element name="caption" namespace="{$outputNamespace}">
          <xsl:value-of select="./docbook:caption"/>
        </xsl:element>
      </xsl:if>
      <xsl:if test="./docbook:thead">
        <xsl:element name="thead" namespace="{$outputNamespace}">
          <xsl:for-each select="./docbook:thead/docbook:tr">
            <xsl:apply-templates select="current()"/>
          </xsl:for-each>
        </xsl:element>
      </xsl:if>
      <xsl:element name="tbody" namespace="{$outputNamespace}">
        <xsl:for-each select="./docbook:tr | ./docbook:tbody/docbook:tr">
          <xsl:apply-templates select="current()"/>
        </xsl:for-each>
      </xsl:element>
      <xsl:if test="./docbook:tfoot">
        <xsl:element name="tfoot" namespace="{$outputNamespace}">
          <xsl:for-each select="./docbook:tfoot/docbook:tr">
            <xsl:apply-templates select="current()"/>
          </xsl:for-each>
        </xsl:element>
      </xsl:if>
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

  <xsl:template match="docbook:th/text()">
    <xsl:call-template name="breakLine">
      <xsl:with-param name="text" select="."/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template match="docbook:th">
    <xsl:element name="th" namespace="{$outputNamespace}">
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:variable name="inlineStyleWidth">
        <xsl:choose>
          <xsl:when test="@ezxhtml:width != ''">
            <xsl:choose>
              <xsl:when test="substring( @ezxhtml:width, string-length( @ezxhtml:width ) ) = '%'">
                <xsl:value-of select="concat( 'width:', @ezxhtml:width, ';' )"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="concat( 'width:', @ezxhtml:width, 'px;' )"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="''"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:variable name="inlineStyleValign">
        <xsl:choose>
          <xsl:when test="@valign">
            <xsl:value-of select="concat( 'vertical-align:', @valign, ';' )"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="''"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
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
      <xsl:if test="$inlineStyleWidth != '' or $inlineStyleValign != ''">
        <xsl:attribute name="style">
          <xsl:value-of select="concat( $inlineStyleWidth, $inlineStyleValign )"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:td/text()">
    <xsl:call-template name="breakLine">
      <xsl:with-param name="text" select="."/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template match="docbook:td">
    <xsl:element name="td" namespace="{$outputNamespace}">
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:variable name="inlineStyleWidth">
        <xsl:choose>
          <xsl:when test="@ezxhtml:width != ''">
            <xsl:choose>
              <xsl:when test="substring( @ezxhtml:width, string-length( @ezxhtml:width ) ) = '%'">
                <xsl:value-of select="concat( 'width:', @ezxhtml:width, ';' )"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="concat( 'width:', @ezxhtml:width, 'px;' )"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="''"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:variable name="inlineStyleValign">
        <xsl:choose>
          <xsl:when test="@valign">
            <xsl:value-of select="concat( 'vertical-align:', @valign, ';' )"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="''"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
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
      <xsl:if test="$inlineStyleWidth != '' or $inlineStyleValign != ''">
        <xsl:attribute name="style">
          <xsl:value-of select="concat( $inlineStyleWidth, $inlineStyleValign )"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:ezembed[ezpayload] | docbook:ezembedinline[ezpayload]">
    <xsl:value-of select="ezpayload/text()" disable-output-escaping="yes"/>
  </xsl:template>

  <xsl:template match="docbook:ezembed | docbook:ezembedinline"/>

  <xsl:template match="docbook:eztemplate[ezpayload] | docbook:eztemplateinline[ezpayload]">
    <xsl:value-of select="ezpayload/text()" disable-output-escaping="yes"/>
  </xsl:template>

  <xsl:template match="docbook:eztemplate | docbook:eztemplateinline"/>

  <xsl:template match="docbook:ezstyle[ezpayload] | docbook:ezstyleinline[ezpayload]">
    <xsl:value-of select="ezpayload/text()" disable-output-escaping="yes"/>
  </xsl:template>

  <xsl:template match="docbook:ezstyle | docbook:ezstyleinline"/>

  <xsl:template name="extractStyleValue">
    <xsl:param name="style"/>
    <xsl:param name="property"/>
    <xsl:value-of select="translate( substring-before( substring-after( concat( substring-after( $style, $property ), ';' ), ':' ), ';' ), ' ', '' )"/>
  </xsl:template>

</xsl:stylesheet>
