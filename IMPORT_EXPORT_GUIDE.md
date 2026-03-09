# Excel/CSV Import & Export Guide

## Overview
The Products CRUD application now supports bulk import and export of products using CSV files (Excel-compatible format).

## Features

### 📥 Download Sample Template
- Click the **"📥 Download Sample"** button in the Products section
- This downloads a CSV file with proper column headers
- Use this as a template for your bulk imports
- Contains one sample row showing the correct format

### 📤 Upload & Import Products
1. Click the **"📤 Upload Products"** button
2. Select a CSV or Excel file from your computer
3. Click **"Upload & Import"**
4. The system will:
   - Parse each row
   - Validate required fields
   - Insert products into the database
   - Show import results (successful imports, skipped rows, errors)

## CSV Format & Columns

The CSV file must have the following columns in this exact order:

| Column # | Field Name | Type | Required | Notes |
|----------|-----------|------|----------|-------|
| 1 | Product Name | String | Yes | Cannot be empty |
| 2 | Category | String | No | e.g., Electronics, Clothing |
| 3 | Brand | String | No | e.g., Nike, Samsung |
| 4 | Material | String | No | e.g., Cotton, Plastic |
| 5 | Price | Number | Yes | Must be > 0 |
| 6 | Available Colors | String | No | Comma or newline separated |
| 7 | Features | String | No | One feature per line |
| 8 | Photo Links | String | No | One URL per line |
| 9 | Video Links | String | No | One URL per line |
| 10 | Delivery & Payment Info | String | No | Multi-line text allowed |

## Example CSV Content

```csv
Product Name,Category,Brand,Material,Price,Available Colors,Features,Photo Links,Video Links,Delivery & Payment Info
Laptop Bag,Accessories,TechBrand,Nylon,2999.99,"Black, Blue, Grey","Waterproof
Lightweight
15 Inch Capacity",https://example.com/photo1.jpg,https://youtube.com/watch?v=xxx,"Free delivery in Kathmandu
Cash on Delivery"
Wireless Earbuds,Electronics,AudioCorp,Plastic,4999.99,"White, Black","30hr Battery
Noise Cancelling","https://example.com/photo1.jpg
https://example.com/photo2.jpg","https://youtube.com/watch?v=yyy
https://vimeo.com/12345","Free shipping
Office hours: 9AM-5PM"
```

## Creating a CSV File

### Using Microsoft Excel
1. Open Excel
2. Enter data in the correct column order
3. File → Save As
4. Choose "CSV (Comma delimited)" format
5. Save and upload

### Using Google Sheets
1. Create a new Google Sheet
2. Enter data in columns
3. File → Download → Comma Separated Values (.csv)
4. Upload to the system

### Using Text Editor
- Create a `.csv` file using any text editor
- Format: `field1,field2,field3,...` per row
- Use quotes for text containing commas: `"multi,part text"`
- Use literal newlines within quotes for multi-line fields

## Formatting Guidelines

### Colors (comma or newline separated)
```
Red, Blue, Black
```

### Features (one per line)
```
Waterproof
Lightweight
Durable
```

### Photo Links (one URL per line)
```
https://example.com/photo1.jpg
https://example.com/photo2.jpg
```

### Video Links (one URL per line)
```
https://youtube.com/watch?v=xxx
https://vimeo.com/123456
```

## Error Handling

The system will:
- ✓ Skip empty rows
- ✓ Validate required fields (Product Name, Price)
- ✓ Show errors for invalid data
- ✓ Continue processing remaining rows even if some fail
- ✓ Display a summary showing successfully imported products and errors

## Validation Rules

1. **Product Name**: Required, cannot be empty
2. **Price**: Required, must be a number greater than 0
3. **Rows**: Each row must have at least a product name and price
4. **Headers**: First row is skipped (assumed to be headers)

## Supported File Types

- ✓ `.csv` - Comma Separated Values
- ✓ Excel files - Recommended to save as CSV for best compatibility
- ✓ UTF-8 encoding with BOM support

## Tips for Best Results

1. **Always download the sample first** to see the exact format
2. **Use descriptive names** - Product names should be clear
3. **Validate links** - Test photo and video URLs before importing
4. **Batch size** - For large imports (1000+), consider splitting into multiple files
5. **Backup** - Always have a backup of your data before bulk import
6. **Check results** - Review imported products for accuracy

## Troubleshooting

### "Please select a valid file"
- Make sure you selected a CSV or Excel file
- File must not be corrupted

### "Row X: Product name is required"
- Cell in column 1 (Product Name) is empty
- All products must have a name

### "Row X: Price must be greater than 0"
- Cell in column 5 (Price) is empty or has invalid value
- Price must be a number greater than zero

### Some rows skipped but no error shown
- Empty rows are automatically skipped (normal behavior)
- Check the import results summary

## Excel Support

The system works best with CSV files. For native Excel (.xlsx) support, you would need to:
1. Install PhpSpreadsheet library via Composer
2. Update ExcelHandler.php to use PhpSpreadsheet

For now, simply save your Excel files as CSV before uploading.

## Database Impact

- Imports create NEW products (no updates to existing products)
- Each row creates a separate product entry
- Duplicate product names are allowed
- All data is stored with JSON encoding for complex fields
