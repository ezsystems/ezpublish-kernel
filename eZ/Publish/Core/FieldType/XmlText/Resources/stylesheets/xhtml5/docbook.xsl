<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ezxhtml5="http://ez.no/namespaces/ezpublish5/xhtml5"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns="http://docbook.org/ns/docbook"
    exclude-result-prefixes="ezxhtml5"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>

  <xsl:template match="ezxhtml5:article">
    <article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
      <xsl:apply-templates/>
    </article>
  </xsl:template>

  <xsl:template match="ezxhtml5:section">
    <section>
      <xsl:apply-templates/>
    </section>
  </xsl:template>

  <xsl:template match="ezxhtml5:p">
    <xsl:choose>
      <xsl:when test="child::ezxhtml5:br">
        <para>
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
        </para>
      </xsl:when>
      <xsl:otherwise>
        <para>
          <xsl:apply-templates/>
        </para>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="ezxhtml5:em">
    <emphasis>
      <xsl:apply-templates/>
    </emphasis>
  </xsl:template>

  <xsl:template match="ezxhtml5:strong">
    <emphasis>
      <xsl:attribute name="role">strong</xsl:attribute>
      <xsl:apply-templates/>
    </emphasis>
  </xsl:template>

  <xsl:template match="ezxhtml5:u">
    <emphasis>
      <xsl:attribute name="role">underlined</xsl:attribute>
      <xsl:apply-templates/>
    </emphasis>
  </xsl:template>

  <xsl:template name="link.href">
    <link>
      <xsl:choose>
        <xsl:when test="starts-with( @href, '#' ) or starts-with( @href, 'ezurl://' ) or starts-with( @href, 'ezlocation://' ) or starts-with( @href, 'ezcontent://' )">
          <xsl:attribute name="xlink:href">
            <xsl:value-of select="@href"/>
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
      <xsl:apply-templates/>
    </title>
  </xsl:template>

  <xsl:template match="ezxhtml5:ol">
    <orderedlist>
      <xsl:apply-templates/>
    </orderedlist>
  </xsl:template>

  <xsl:template match="ezxhtml5:ul">
    <itemizedlist>
      <xsl:apply-templates/>
    </itemizedlist>
  </xsl:template>

  <xsl:template match="ezxhtml5:ol/ezxhtml5:li | ezxhtml5:ul/ezxhtml5:li">
    <listitem>
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
    <xsl:element name="{$tablename}">
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
      <xsl:if test="$tablename = 'table'">
        <caption>
          <xsl:value-of select="./ezxhtml5:caption"/>
        </caption>
      </xsl:if>
      <tbody>
        <xsl:for-each select="./ezxhtml5:tr | ./ezxhtml5:tbody/ezxhtml5:tr">
          <xsl:apply-templates select="current()"/>
        </xsl:for-each>
      </tbody>
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
    </th>
  </xsl:template>

  <xsl:template match="ezxhtml5:td">
    <td>
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
    </td>
  </xsl:template>
</xsl:stylesheet>
