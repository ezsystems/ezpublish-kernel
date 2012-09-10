<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/">
<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="no" />

<xsl:template match="/">
<section><xsl:copy-of select="@*"/>
<xsl:apply-templates/>
</section>
</xsl:template>

<xsl:template match="p | para">
<paragraph><xsl:copy-of select="@*"/><xsl:apply-templates/></paragraph>
</xsl:template>

<xsl:template match="b | bold">
<strong><xsl:copy-of select="@*"/><xsl:apply-templates/></strong>
</xsl:template>

<xsl:template match="i | em">
<emphasize><xsl:copy-of select="@*"/><xsl:apply-templates/></emphasize>
</xsl:template>

<xsl:template match="a">
<link><xsl:copy-of select="@*"/><xsl:apply-templates/></link>
</xsl:template>

<xsl:template match="h">
<header><xsl:copy-of select="@*"/><xsl:apply-templates/></header>
</xsl:template>

</xsl:stylesheet>
