<?xml version="1.0" encoding="UTF-8"?>
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

  <xsl:template match="docbook:section">
    <section>
      <xsl:apply-templates/>
    </section>
  </xsl:template>

  <xsl:template match="docbook:para">
    <paragraph>
      <xsl:if test="@ezxhtml:class">
        <xsl:attribute name="class">
          <xsl:value-of select="@ezxhtml:class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@ezxhtml:textalign">
        <xsl:attribute name="align">
          <xsl:value-of select="@ezxhtml:textalign"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
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
          <xsl:if test="@ezxhtml:class">
            <xsl:attribute name="class">
              <xsl:value-of select="@ezxhtml:class"/>
            </xsl:attribute>
          </xsl:if>
          <xsl:apply-templates/>
        </strong>
      </xsl:when>
      <xsl:when test="@role='underlined'">
        <custom name="underline">
          <xsl:apply-templates/>
        </custom>
      </xsl:when>
      <xsl:otherwise>
        <emphasize>
          <xsl:if test="@ezxhtml:class">
            <xsl:attribute name="class">
              <xsl:value-of select="@ezxhtml:class"/>
            </xsl:attribute>
          </xsl:if>
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
      <xsl:choose>
        <xsl:when test="starts-with( @xlink:href, 'ezurl://' )">
          <xsl:attribute name="url_id">
            <xsl:value-of select="substring-before( concat( substring-after( @xlink:href, 'ezurl://' ), '#' ), '#' )"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:when test="starts-with( @xlink:href, 'ezcontent://' )">
          <xsl:attribute name="object_id">
            <xsl:value-of select="substring-before( concat( substring-after( @xlink:href, 'ezcontent://' ), '#' ), '#' )"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:when test="starts-with( @xlink:href, 'ezlocation://' )">
          <xsl:attribute name="node_id">
            <xsl:value-of select="substring-before( concat( substring-after( @xlink:href, 'ezlocation://' ), '#' ), '#' )"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:when test="starts-with( @xlink:href, '#' )"/>
        <xsl:otherwise>
          <xsl:attribute name="href">
            <xsl:value-of select="substring-before( concat( @xlink:href, '#' ), '#' )"/>
          </xsl:attribute>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="contains( @xlink:href, '#' )">
        <xsl:attribute name="anchor_name">
          <xsl:value-of select="substring-after( @xlink:href, '#' )"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xlink:show = 'new'">
        <xsl:attribute name="target">
          <xsl:value-of select="'_blank'"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xml:id">
        <xsl:attribute name="xhtml:id">
          <xsl:value-of select="@xml:id"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xlink:title">
        <xsl:attribute name="xhtml:title">
          <xsl:value-of select="@xlink:title"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@ezxhtml:class">
        <xsl:attribute name="class">
          <xsl:value-of select="@ezxhtml:class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </link>
  </xsl:template>

  <xsl:template match="docbook:title">
    <header>
      <xsl:if test="@ezxhtml:class">
        <xsl:attribute name="class">
          <xsl:value-of select="@ezxhtml:class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@ezxhtml:textalign">
        <xsl:attribute name="align">
          <xsl:value-of select="@ezxhtml:textalign"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </header>
  </xsl:template>

  <xsl:template match="docbook:orderedlist">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <ol>
        <xsl:if test="@ezxhtml:class">
          <xsl:attribute name="class">
            <xsl:value-of select="@ezxhtml:class"/>
          </xsl:attribute>
        </xsl:if>
        <xsl:apply-templates/>
      </ol>
    </paragraph>
  </xsl:template>

  <xsl:template match="docbook:itemizedlist">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <ul>
        <xsl:if test="@ezxhtml:class">
          <xsl:attribute name="class">
            <xsl:value-of select="@ezxhtml:class"/>
          </xsl:attribute>
        </xsl:if>
        <xsl:apply-templates/>
      </ul>
    </paragraph>
  </xsl:template>

  <xsl:template match="docbook:itemizedlist/docbook:listitem/docbook:para | docbook:orderedlist/docbook:listitem/docbook:para">
    <li>
      <xsl:if test="../@ezxhtml:class">
        <xsl:attribute name="class">
          <xsl:value-of select="../@ezxhtml:class"/>
        </xsl:attribute>
      </xsl:if>
      <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
        <xsl:apply-templates/>
      </paragraph>
    </li>
  </xsl:template>

  <xsl:template match="docbook:table | docbook:informaltable">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <table>
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
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </tr>
  </xsl:template>

  <xsl:template match="docbook:th">
    <th>
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
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
      <xsl:apply-templates/>
    </th>
  </xsl:template>

  <xsl:template match="docbook:td">
    <td>
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
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
      <xsl:apply-templates/>
    </td>
  </xsl:template>

  <xsl:template match="ezcustom:custom">
    <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
      <xsl:element name="custom">
        <xsl:for-each select="@ezcustom:*">
          <xsl:choose>
            <xsl:when test="local-name() = 'name'">
              <xsl:attribute name="name">
                <xsl:value-of select="current()"/>
              </xsl:attribute>
            </xsl:when>
            <xsl:otherwise>
              <xsl:attribute name="custom:{local-name()}">
                <xsl:value-of select="current()"/>
              </xsl:attribute>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </xsl:element>
    </paragraph>
  </xsl:template>

  <xsl:template name="extractStyleValue">
    <xsl:param name="style"/>
    <xsl:param name="property"/>
    <xsl:value-of select="translate( substring-before( substring-after( concat( substring-after( $style, $property ), ';' ), ':' ), ';' ), ' ', '' )"/>
  </xsl:template>

</xsl:stylesheet>
