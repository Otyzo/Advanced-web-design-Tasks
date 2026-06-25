<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" indent="yes"/>

<xsl:template match="/catalog">
  <html>
  <head>
    <title>ShopMart Product Catalog (XML Source)</title>
    <style>
      body { font-family: Segoe UI, sans-serif; background:#1c1f26; color:#e7e9ec; padding:24px; }
      h1 { color:#10b981; }
      table { width:100%; border-collapse: collapse; margin-top:16px; }
      th, td { border:1px solid #343a46; padding:10px; text-align:left; font-size:14px; }
      th { background:#242831; color:#10b981; }
      tr:nth-child(even) { background:#20242c; }
    </style>
  </head>
  <body>
    <h1>ShopMart Product Catalog — Raw XML Data</h1>
    <p>This table is generated directly from <code>products.xml</code> using XSLT, demonstrating that the XML is well-formed and machine-readable.</p>
    <table>
      <tr>
        <th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Description</th>
      </tr>
      <xsl:for-each select="product">
      <tr>
        <td><xsl:value-of select="@id"/></td>
        <td><xsl:value-of select="name"/></td>
        <td><xsl:value-of select="category"/></td>
        <td><xsl:value-of select="price/@currency"/> <xsl:value-of select="price"/></td>
        <td><xsl:value-of select="stock"/></td>
        <td><xsl:value-of select="description"/></td>
      </tr>
      </xsl:for-each>
    </table>
  </body>
  </html>
</xsl:template>
</xsl:stylesheet>
