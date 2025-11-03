export function useExcelExport() {
  /**
   * Export data to Excel file
   * @param {Array} data - Array of objects to export
   * @param {String} filename - Name of the file (without extension)
   * @param {String} sheetName - Name of the worksheet
   */
  async function exportToExcel(data, filename = 'relatorio', sheetName = 'Dados') {
    try {
      // Dynamic import of xlsx library
      const XLSXModule = await import('xlsx');
      const XLSX = XLSXModule.default || XLSXModule;

      // Create a new workbook
      const wb = XLSX.utils.book_new();

      // Convert data to worksheet
      const ws = XLSX.utils.json_to_sheet(data);

      // Auto-size columns
      const maxWidth = 50;
      const cols = [];

      if (data.length > 0) {
        Object.keys(data[0]).forEach(key => {
          const maxLength = Math.max(
            key.length,
            ...data.map(row => String(row[key] || '').length)
          );
          cols.push({ wch: Math.min(maxLength + 2, maxWidth) });
        });
        ws['!cols'] = cols;
      }

      // Add worksheet to workbook
      XLSX.utils.book_append_sheet(wb, ws, sheetName);

      // Generate filename with timestamp
      const timestamp = new Date().toISOString().split('T')[0];
      const fullFilename = `${filename}_${timestamp}.xlsx`;

      // Write and download file
      XLSX.writeFile(wb, fullFilename);

      return true;
    } catch (error) {
      console.error('Erro ao exportar para Excel:', error);
      return false;
    }
  }

  /**
   * Export multiple sheets to Excel file
   * @param {Array} sheets - Array of {data, sheetName} objects
   * @param {String} filename - Name of the file (without extension)
   */
  async function exportMultipleSheetsToExcel(sheets, filename = 'relatorio') {
    try {
      // Dynamic import of xlsx library
      const XLSXModule = await import('xlsx');
      const XLSX = XLSXModule.default || XLSXModule;

      // Create a new workbook
      const wb = XLSX.utils.book_new();

      sheets.forEach(sheet => {
        // Convert data to worksheet
        const ws = XLSX.utils.json_to_sheet(sheet.data);

        // Auto-size columns
        const maxWidth = 50;
        const cols = [];

        if (sheet.data.length > 0) {
          Object.keys(sheet.data[0]).forEach(key => {
            const maxLength = Math.max(
              key.length,
              ...sheet.data.map(row => String(row[key] || '').length)
            );
            cols.push({ wch: Math.min(maxLength + 2, maxWidth) });
          });
          ws['!cols'] = cols;
        }

        // Add worksheet to workbook
        XLSX.utils.book_append_sheet(wb, ws, sheet.sheetName);
      });

      // Generate filename with timestamp
      const timestamp = new Date().toISOString().split('T')[0];
      const fullFilename = `${filename}_${timestamp}.xlsx`;

      // Write and download file
      XLSX.writeFile(wb, fullFilename);

      return true;
    } catch (error) {
      console.error('Erro ao exportar para Excel:', error);
      return false;
    }
  }

  /**
   * Capture chart as base64 image
   * @param {String} chartSelector - CSS selector for the chart container
   * @returns {Promise<String>} Base64 encoded image
   */
  async function captureChartAsImage(chartSelector) {
    try {
      const chartElement = document.querySelector(chartSelector);
      if (!chartElement) {
        console.warn(`Chart not found: ${chartSelector}`);
        return null;
      }

      // Get the SVG element from ApexCharts
      const svgElement = chartElement.querySelector('svg');
      if (!svgElement) {
        console.warn(`SVG not found in chart: ${chartSelector}`);
        return null;
      }

      // Clone the SVG to avoid modifying the original
      const clonedSvg = svgElement.cloneNode(true);

      // Get computed styles
      const computedStyle = window.getComputedStyle(svgElement);
      const width = parseInt(computedStyle.width) || 800;
      const height = parseInt(computedStyle.height) || 400;

      // Remove any external references that might cause CORS issues
      removeExternalReferences(clonedSvg);

      // Convert SVG to data URL directly (safer approach)
      const svgData = new XMLSerializer().serializeToString(clonedSvg);
      const svgDataUrl = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));

      // Create canvas
      const canvas = document.createElement('canvas');
      const scale = 2; // Higher resolution
      canvas.width = width * scale;
      canvas.height = height * scale;
      const ctx = canvas.getContext('2d', { willReadFrequently: false });

      // Scale for better quality
      ctx.scale(scale, scale);

      // White background
      ctx.fillStyle = '#FFFFFF';
      ctx.fillRect(0, 0, width, height);

      // Load SVG into image
      return new Promise((resolve) => {
        const img = new Image();

        img.onload = () => {
          try {
            ctx.drawImage(img, 0, 0, width, height);
            const base64 = canvas.toDataURL('image/png').split(',')[1];
            resolve(base64);
          } catch (err) {
            console.error('Error converting canvas to data URL:', err);
            // Fallback: return SVG as base64
            const svgBase64 = btoa(unescape(encodeURIComponent(svgData)));
            resolve(svgBase64);
          }
        };

        img.onerror = (err) => {
          console.warn(`Failed to load SVG image: ${chartSelector}`, err);
          // Fallback: return SVG as base64
          const svgBase64 = btoa(unescape(encodeURIComponent(svgData)));
          resolve(svgBase64);
        };

        img.src = svgDataUrl;
      });
    } catch (error) {
      console.error('Error capturing chart:', error);
      return null;
    }
  }

  /**
   * Inline all styles in SVG to avoid CORS issues
   * @param {SVGElement} svg - SVG element to process
   */
  function inlineStyles(svg) {
    // Only inline essential styles to avoid CORS issues
    const elements = svg.querySelectorAll('*');
    elements.forEach(el => {
      try {
        const computedStyle = window.getComputedStyle(el);

        // Only copy essential visual properties
        const essentialProps = ['fill', 'stroke', 'stroke-width', 'font-family', 'font-size', 'font-weight', 'color'];
        const styles = [];

        essentialProps.forEach(prop => {
          const value = computedStyle.getPropertyValue(prop);
          if (value && value !== 'none' && value !== 'normal' && value !== '') {
            styles.push(`${prop}:${value}`);
          }
        });

        if (styles.length > 0) {
          const existingStyle = el.getAttribute('style') || '';
          el.setAttribute('style', existingStyle + ';' + styles.join(';'));
        }
      } catch (err) {
        // Ignore elements that can't be styled
        console.debug('Could not inline styles for element:', el);
      }
    });
  }

  /**
   * Remove external references from SVG
   * @param {SVGElement} svg - SVG element to process
   */
  function removeExternalReferences(svg) {
    // Remove external font references
    const foreignObjects = svg.querySelectorAll('foreignObject');
    foreignObjects.forEach(fo => fo.remove());

    // Remove xlink:href references that might cause CORS
    const elements = svg.querySelectorAll('[*|href]');
    elements.forEach(el => {
      const href = el.getAttribute('href') || el.getAttribute('xlink:href');
      if (href && (href.startsWith('http://') || href.startsWith('https://'))) {
        el.removeAttribute('href');
        el.removeAttribute('xlink:href');
      }
    });
  }

  /**
   * Export multiple sheets with charts to Excel file using ExcelJS
   * @param {Array} sheets - Array of {data, sheetName, charts} objects
   * @param {String} filename - Name of the file (without extension)
   * @param {Array} chartSelectors - Array of {selector, sheetName, title} for charts to capture
   */
  async function exportWithCharts(sheets, filename = 'relatorio', chartSelectors = []) {
    try {
      // Dynamic import of ExcelJS library
      const ExcelJSModule = await import('exceljs');
      const ExcelJS = ExcelJSModule.default || ExcelJSModule;

      // Create a new workbook
      const workbook = new ExcelJS.Workbook();

      // Set workbook properties
      workbook.creator = 'Sistema de Hemodiálise';
      workbook.created = new Date();

      // Process data sheets
      for (const sheet of sheets) {
        const worksheet = workbook.addWorksheet(sheet.sheetName);

        if (sheet.data.length > 0) {
          // Get headers from first data row
          const headers = Object.keys(sheet.data[0]);

          // Add header row with styling
          const headerRow = worksheet.addRow(headers);
          headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' } };
          headerRow.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF4472C4' }
          };
          headerRow.alignment = { vertical: 'middle', horizontal: 'center' };

          // Add data rows
          sheet.data.forEach(row => {
            const values = headers.map(header => row[header]);
            worksheet.addRow(values);
          });

          // Auto-fit columns
          worksheet.columns.forEach((column, index) => {
            const header = headers[index];
            const maxLength = Math.max(
              header.length,
              ...sheet.data.map(row => String(row[header] || '').length)
            );
            column.width = Math.min(maxLength + 2, 50);
          });

          // Add borders to all cells
          worksheet.eachRow((row, rowNumber) => {
            row.eachCell((cell) => {
              cell.border = {
                top: { style: 'thin' },
                left: { style: 'thin' },
                bottom: { style: 'thin' },
                right: { style: 'thin' }
              };
            });
          });
        }
      }

      // Capture and add chart images
      if (chartSelectors && chartSelectors.length > 0) {
        for (const chartInfo of chartSelectors) {
          const imageData = await captureChartAsImage(chartInfo.selector);

          if (imageData) {
            // Create a worksheet for the chart
            const chartWorksheet = workbook.addWorksheet(chartInfo.sheetName);

            // Add title
            chartWorksheet.mergeCells('A1:H1');
            const titleCell = chartWorksheet.getCell('A1');
            titleCell.value = chartInfo.title || 'Gráfico';
            titleCell.font = { size: 16, bold: true };
            titleCell.alignment = { horizontal: 'center', vertical: 'middle' };
            chartWorksheet.getRow(1).height = 30;

            // Add the image to the worksheet
            const imageId = workbook.addImage({
              base64: imageData,
              extension: 'png',
            });

            // Position the image (starting at row 3, column A)
            chartWorksheet.addImage(imageId, {
              tl: { col: 0, row: 2 },
              ext: { width: 800, height: 400 }
            });

            // Set row heights to accommodate image
            for (let i = 3; i <= 25; i++) {
              chartWorksheet.getRow(i).height = 20;
            }

            // Add note below the image
            const noteRow = 28;
            chartWorksheet.getCell(`A${noteRow}`).value = 'Nota: Os dados brutos deste gráfico estão disponíveis nas outras abas.';
            chartWorksheet.getCell(`A${noteRow}`).font = { italic: true, color: { argb: 'FF666666' } };
          }
        }
      }

      // Generate filename with timestamp
      const timestamp = new Date().toISOString().split('T')[0];
      const fullFilename = `${filename}_${timestamp}.xlsx`;

      // Write to buffer
      const buffer = await workbook.xlsx.writeBuffer();

      // Create blob and download
      const blob = new Blob([buffer], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = fullFilename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);

      return true;
    } catch (error) {
      console.error('Erro ao exportar com gráficos:', error);
      return false;
    }
  }

  /**
   * Download base64 image as file
   * @param {String} base64Data - Base64 encoded image
   * @param {String} filename - Name of the file
   */
  function downloadImage(base64Data, filename) {
    try {
      const link = document.createElement('a');
      link.href = `data:image/png;base64,${base64Data}`;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    } catch (error) {
      console.error('Error downloading image:', error);
    }
  }

  return {
    exportToExcel,
    exportMultipleSheetsToExcel,
    exportWithCharts,
    captureChartAsImage
  };
}
