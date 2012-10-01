<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" exclude-result-prefixes="xhtml custom image">
<xsl:output method="html" indent="yes" encoding="UTF-8" />

<xsl:template match="custom[@name='underline']">
<u><xsl:apply-templates/></u>
</xsl:template>

<xsl:template match="custom[@name='sup'] | custom[@name='sub']">
<xsl:element name="{@name}"><xsl:apply-templates/></xsl:element>
</xsl:template>

</xsl:stylesheet>
