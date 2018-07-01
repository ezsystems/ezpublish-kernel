<?xml version="1.0" encoding="UTF-8"?>
<!-- Deprecated in version 7.2 -->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:docbook="http://docbook.org/ns/docbook"
    xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml"
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
    xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
    xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    exclude-result-prefixes="docbook xlink ezxhtml ezcustom"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>

  <xsl:template match="/docbook:section">
    <section
        xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
        xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
        xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
      <xsl:apply-templates/>
    </section>
  </xsl:template>

  <!-- unwrap sections as in legacy ezxml they are used for heading levels only -->
  <xsl:template match="docbook:section">
    <!--section-->
      <xsl:apply-templates/>
    <!--/section-->
  </xsl:template>

  <xsl:template match="docbook:para">
    <paragraph>
      <xsl:call-template name="addAttributeClassEzxhtml"/>
      <xsl:if test="@ezxhtml:textalign">
        <xsl:attribute name="align">
          <xsl:value-of select="@ezxhtml:textalign"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </paragraph>
  </xsl:template>

  <xsl:template match="docbook:blockquote">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <custom name="quote">
        <xsl:apply-templates/>
      </custom>
    </paragraph>
  </xsl:template>

  <xsl:template name="breakLine">
    <xsl:param name="text"/>
    <xsl:variable name="newLine">
      <xsl:text>&#xa;</xsl:text>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="contains( $text, $newLine )">
        <xsl:value-of select="substring-before( $text, $newLine )"/>
        <xsl:text disable-output-escaping="yes">&lt;/line&gt;&lt;line&gt;</xsl:text>
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
        <xsl:when test="$nodes[1][last()]/self::text()">
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
    <line>
      <xsl:call-template name="paragraphLiterallayout">
        <xsl:with-param name="nodes" select="node()"/>
      </xsl:call-template>
    </line>
  </xsl:template>

  <xsl:template match="docbook:emphasis">
    <xsl:choose>
      <xsl:when test="@role='strong'">
        <strong>
          <xsl:call-template name="addAttributeClassEzxhtml"/>
          <xsl:apply-templates/>
        </strong>
      </xsl:when>
      <xsl:when test="@role='underlined'">
        <custom name="underline">
          <xsl:apply-templates/>
        </custom>
      </xsl:when>
      <xsl:when test="@role='strikedthrough'">
        <custom name="strike">
          <xsl:apply-templates/>
        </custom>
      </xsl:when>
      <xsl:otherwise>
        <emphasize>
          <xsl:call-template name="addAttributeClassEzxhtml"/>
          <xsl:apply-templates/>
        </emphasize>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="docbook:subscript">
    <custom name="sub">
      <xsl:apply-templates/>
    </custom>
  </xsl:template>

  <xsl:template match="docbook:superscript">
    <custom name="sup">
      <xsl:apply-templates/>
    </custom>
  </xsl:template>

  <xsl:template match="docbook:anchor">
    <anchor>
      <xsl:attribute name="name">
        <xsl:value-of select="@xml:id"/>
      </xsl:attribute>
    </anchor>
  </xsl:template>

  <xsl:template match="docbook:link[@xlink:href]">
    <link>
      <xsl:call-template name="addLinkAttributes">
        <xsl:with-param name="vector" select="."/>
      </xsl:call-template>
      <xsl:call-template name="addAttributeClassEzxhtml"/>
      <xsl:if test="@xml:id">
        <xsl:attribute name="xhtml:id">
          <xsl:value-of select="@xml:id"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </link>
  </xsl:template>

  <xsl:template name="addLinkAttributes">
    <xsl:param name="vector"/>
    <xsl:choose>
      <xsl:when test="starts-with( $vector/@xlink:href, 'ezurl://' )">
        <xsl:attribute name="url_id">
          <xsl:value-of select="substring-before( concat( substring-after( $vector/@xlink:href, 'ezurl://' ), '#' ), '#' )"/>
        </xsl:attribute>
      </xsl:when>
      <xsl:when test="starts-with( $vector/@xlink:href, 'ezcontent://' )">
        <xsl:call-template name="addAttributeObjectId">
          <xsl:with-param name="href" select="$vector/@xlink:href"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="starts-with( $vector/@xlink:href, 'ezlocation://' )">
        <xsl:call-template name="addAttributeNodeId">
          <xsl:with-param name="href" select="$vector/@xlink:href"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="starts-with( $vector/@xlink:href, '#' )"/>
      <!-- rather throw an error here, and remove preceding when -->
      <xsl:otherwise>
        <xsl:attribute name="href">
          <xsl:value-of select="substring-before( concat( $vector/@xlink:href, '#' ), '#' )"/>
        </xsl:attribute>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:if test="contains( $vector/@xlink:href, '#' )">
      <xsl:attribute name="anchor_name">
        <xsl:value-of select="substring-after( $vector/@xlink:href, '#' )"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:if test="$vector/@xlink:show = 'new'">
      <xsl:attribute name="target">_blank</xsl:attribute>
    </xsl:if>
    <xsl:if test="$vector/@xlink:title">
      <xsl:attribute name="xhtml:title">
        <xsl:value-of select="$vector/@xlink:title"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template match="docbook:title">
    <xsl:variable name="headingLevel">
      <xsl:choose>
        <xsl:when test="@ezxhtml:level">
          <xsl:variable name="levelAttribute">
            <xsl:value-of select="@ezxhtml:level - 1"/>
          </xsl:variable>
          <xsl:choose>
            <xsl:when test="$levelAttribute = 0">
              <xsl:value-of select="1"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="$levelAttribute"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:when>
        <xsl:otherwise>
          <xsl:variable name="levelAttribute">
            <xsl:value-of select="count( ancestor::docbook:section )"/>
          </xsl:variable>
          <xsl:choose>
            <xsl:when test="$levelAttribute &gt; 5">
              <xsl:value-of select="5"/>
            </xsl:when>
            <xsl:when test="$levelAttribute &gt; 1">
              <xsl:value-of select="$levelAttribute - 1"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="$levelAttribute"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:call-template name="recursiveWrapHeadingInSection">
      <xsl:with-param name="node" select="node()"/>
      <xsl:with-param name="level" select="$headingLevel"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template match="docbook:orderedlist">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <ol>
        <xsl:call-template name="addAttributeClassEzxhtml"/>
        <xsl:apply-templates/>
      </ol>
    </paragraph>
  </xsl:template>

  <xsl:template match="docbook:itemizedlist">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <ul>
        <xsl:call-template name="addAttributeClassEzxhtml"/>
        <xsl:apply-templates/>
      </ul>
    </paragraph>
  </xsl:template>

  <xsl:template match="docbook:itemizedlist/docbook:listitem/docbook:para | docbook:orderedlist/docbook:listitem/docbook:para">
    <li>
      <xsl:call-template name="addClassAttribute">
        <xsl:with-param name="class" select="../@ezxhtml:class"/>
      </xsl:call-template>
      <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
        <xsl:apply-templates/>
      </paragraph>
    </li>
  </xsl:template>

  <xsl:template match="docbook:table | docbook:informaltable">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <table>
        <xsl:call-template name="addAttributeClassNoname"/>
        <xsl:if test="@width">
          <xsl:attribute name="width">
            <xsl:value-of select="@width"/>
          </xsl:attribute>
        </xsl:if>
        <xsl:attribute name="border">
          <xsl:choose>
            <xsl:when test="contains( @style, 'border-width' )">
              <xsl:variable name="borderWidth">
                <xsl:call-template name="extractStyleValue">
                  <xsl:with-param name="style" select="@style"/>
                  <xsl:with-param name="property" select="'border-width'"/>
                </xsl:call-template>
              </xsl:variable>
              <xsl:choose>
                <xsl:when test="substring( $borderWidth, string-length( $borderWidth ) - 1 ) = 'px'">
                  <xsl:value-of select="substring-before( $borderWidth, 'px' )"/>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:value-of select="$borderWidth"/>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>0</xsl:text>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <xsl:attribute name="custom:summary">
          <xsl:value-of select="@title"/>
        </xsl:attribute>
        <xsl:attribute name="custom:caption">
          <xsl:value-of select="./docbook:caption"/>
        </xsl:attribute>
        <xsl:for-each select="./docbook:tr | ./docbook:tbody/docbook:tr">
          <xsl:apply-templates select="current()"/>
        </xsl:for-each>
      </table>
    </paragraph>
  </xsl:template>

  <xsl:template match="docbook:tr">
    <tr>
      <xsl:call-template name="addAttributeClassNoname"/>
      <xsl:apply-templates/>
    </tr>
  </xsl:template>

  <xsl:template match="docbook:th">
    <th>
      <xsl:call-template name="addAttributeClassNoname"/>
      <xsl:if test="@ezxhtml:width">
        <xsl:attribute name="xhtml:width">
          <xsl:value-of select="@ezxhtml:width"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@valign">
        <xsl:attribute name="custom:valign">
          <xsl:value-of select="@valign"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@colspan">
        <xsl:attribute name="xhtml:colspan">
          <xsl:value-of select="@colspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@rowspan">
        <xsl:attribute name="xhtml:rowspan">
          <xsl:value-of select="@rowspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@abbr">
        <xsl:attribute name="custom:abbr">
          <xsl:value-of select="@abbr"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@scope">
        <xsl:attribute name="custom:scope">
          <xsl:value-of select="@scope"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:for-each select="./* | ./text()">
        <xsl:call-template name="wrapInParagraph">
          <xsl:with-param name="node" select="current()"/>
        </xsl:call-template>
      </xsl:for-each>
    </th>
  </xsl:template>

  <xsl:template match="docbook:td">
    <td>
      <xsl:call-template name="addAttributeClassNoname"/>
      <xsl:if test="@ezxhtml:width">
        <xsl:attribute name="xhtml:width">
          <xsl:value-of select="@ezxhtml:width"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@valign">
        <xsl:attribute name="custom:valign">
          <xsl:value-of select="@valign"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@colspan">
        <xsl:attribute name="xhtml:colspan">
          <xsl:value-of select="@colspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@rowspan">
        <xsl:attribute name="xhtml:rowspan">
          <xsl:value-of select="@rowspan"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:for-each select="./* | ./text()">
        <xsl:call-template name="wrapInParagraph">
          <xsl:with-param name="node" select="current()"/>
        </xsl:call-template>
      </xsl:for-each>
    </td>
  </xsl:template>

  <xsl:template match="docbook:ezembed">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <xsl:call-template name="embed"/>
    </paragraph>
  </xsl:template>

  <xsl:template match="docbook:ezembedinline">
    <xsl:call-template name="embed"/>
  </xsl:template>

  <xsl:template name="embed">
    <xsl:choose>
      <xsl:when test="docbook:ezlink">
        <xsl:call-template name="linkedEmbed"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="embedTyped"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="embedTyped">
    <xsl:choose>
      <xsl:when test="local-name() = 'ezembed'">
        <xsl:element name="embed">
          <xsl:call-template name="embedBase"/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="embed-inline">
          <xsl:call-template name="embedBase"/>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="embedBase">
    <xsl:if test="@xml:id">
      <xsl:attribute name="xhtml:id">
        <xsl:value-of select="@xml:id"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:call-template name="addEmbedTargetAttribute"/>
    <xsl:if test="@view">
      <xsl:attribute name="view">
        <xsl:value-of select="@view"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:call-template name="addAttributeClassEzxhtml"/>
    <xsl:if test="@ezxhtml:align">
      <xsl:attribute name="align">
        <xsl:value-of select="@ezxhtml:align"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:for-each select="./docbook:ezconfig/docbook:ezvalue">
      <xsl:choose>
        <xsl:when test="@key = 'size'">
          <xsl:attribute name="{@key}">
            <xsl:value-of select="current()"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:otherwise>
          <xsl:attribute name="custom:{@key}">
            <xsl:value-of select="current()"/>
          </xsl:attribute>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="addEmbedTargetAttribute">
    <xsl:choose>
      <xsl:when test="starts-with( @xlink:href, 'ezcontent://' )">
        <xsl:call-template name="addAttributeObjectId">
          <xsl:with-param name="href" select="@xlink:href"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="starts-with( @xlink:href, 'ezlocation://' )">
        <xsl:call-template name="addAttributeNodeId">
          <xsl:with-param name="href" select="@xlink:href"/>
        </xsl:call-template>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="linkedEmbed">
    <link>
      <xsl:call-template name="addLinkAttributes">
        <xsl:with-param name="vector" select="./docbook:ezlink"/>
      </xsl:call-template>
      <xsl:if test="./docbook:ezlink/@xml:id">
        <xsl:attribute name="xhtml:id">
          <xsl:value-of select="./docbook:ezlink/@xml:id"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="./docbook:ezlink/@ezxhtml:class">
        <xsl:attribute name="class">
          <xsl:value-of select="./docbook:ezlink/@ezxhtml:class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:call-template name="embedTyped"/>
    </link>
  </xsl:template>

  <xsl:template match="docbook:eztemplate">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <xsl:element name="custom">
        <xsl:attribute name="name">
          <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:if test="@ezxhtml:class">
          <xsl:attribute name="custom:class">
            <xsl:value-of select="@ezxhtml:class"/>
          </xsl:attribute>
        </xsl:if>
        <xsl:if test="@ezxhtml:align">
          <xsl:attribute name="custom:align">
            <xsl:value-of select="@ezxhtml:align"/>
          </xsl:attribute>
        </xsl:if>
        <xsl:for-each select="./docbook:ezconfig/docbook:ezvalue">
          <xsl:attribute name="custom:{@key}">
            <xsl:value-of select="current()"/>
          </xsl:attribute>
        </xsl:for-each>
        <xsl:apply-templates select="./docbook:ezcontent"/>
      </xsl:element>
    </paragraph>
  </xsl:template>

  <xsl:template match="docbook:eztemplateinline">
    <xsl:element name="custom">
      <xsl:attribute name="name">
        <xsl:value-of select="@name"/>
      </xsl:attribute>
      <xsl:if test="@ezxhtml:class">
        <xsl:attribute name="custom:class">
          <xsl:value-of select="@ezxhtml:class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@ezxhtml:align">
        <xsl:attribute name="custom:align">
          <xsl:value-of select="@ezxhtml:align"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:for-each select="./docbook:ezconfig/docbook:ezvalue">
        <xsl:attribute name="custom:{@key}">
          <xsl:value-of select="current()"/>
        </xsl:attribute>
      </xsl:for-each>
      <xsl:apply-templates select="./docbook:ezcontent"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="docbook:eztemplate/docbook:ezcontent">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template name="addAttributeNodeId">
    <xsl:param name="href"/>
    <xsl:attribute name="node_id">
      <xsl:value-of select="substring-before( concat( substring-after( $href, 'ezlocation://' ), '#' ), '#' )"/>
    </xsl:attribute>
  </xsl:template>

  <xsl:template name="addAttributeObjectId">
    <xsl:param name="href"/>
    <xsl:attribute name="object_id">
      <xsl:value-of select="substring-before( concat( substring-after( $href, 'ezcontent://' ), '#' ), '#' )"/>
    </xsl:attribute>
  </xsl:template>

  <xsl:template name="addAttributeClassEzxhtml">
    <xsl:call-template name="addClassAttribute">
      <xsl:with-param name="class" select="@ezxhtml:class"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="addAttributeClassNoname">
    <xsl:call-template name="addClassAttribute">
      <xsl:with-param name="class" select="@class"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="addClassAttribute">
    <xsl:param name="class"/>
    <xsl:if test="$class">
      <xsl:attribute name="class">
        <xsl:value-of select="$class"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template name="extractStyleValue">
    <xsl:param name="style"/>
    <xsl:param name="property"/>
    <xsl:value-of select="translate( substring-before( substring-after( concat( substring-after( $style, $property ), ';' ), ':' ), ';' ), ' ', '' )"/>
  </xsl:template>

  <xsl:template name="wrapInParagraph">
    <xsl:param name="node"/>
    <xsl:choose>
      <xsl:when test="$node/self::text()">
        <paragraph>
          <xsl:apply-templates select="$node"/>
        </paragraph>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates select="$node"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="recursiveWrapHeadingInSection">
    <xsl:param name="node"/>
    <xsl:param name="level"/>
    <xsl:choose>
      <xsl:when test="$level &gt; 0">
        <section>
          <xsl:call-template name="recursiveWrapHeadingInSection">
            <xsl:with-param name="node" select="$node"/>
            <xsl:with-param name="level" select="$level - 1"/>
          </xsl:call-template>
        </section>
      </xsl:when>
      <xsl:otherwise>
        <header>
          <xsl:call-template name="addAttributeClassEzxhtml"/>
          <xsl:if test="@ezxhtml:textalign">
            <xsl:attribute name="align">
              <xsl:value-of select="@ezxhtml:textalign"/>
            </xsl:attribute>
          </xsl:if>
          <xsl:apply-templates/>
        </header>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
