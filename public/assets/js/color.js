function ColorworkViewModel(projectId) {
    const self = this;
    const baseUrl = document.body.dataset.baseUrl;

    self.project = ko.observable({});
    self.rowCount = ko.observable(0);
    self.modeToggle = ko.observable(false); // checkbox binding
    self.stitchShape = ko.observable('square');
    self.mode = ko.observable('screenshot'); // 'image' or 'custom'
    self.width = ko.observable(0);
    self.height = ko.observable(0);
    self.maxSize = 50;
    self.chart = ko.observableArray([]);

    self.loadProject = function() {
        fetch(`${baseUrl}projects/color_data/${projectId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    self.project(data.project);
                    self.stitchShape(data.stitch_shape);
                    self.mode(data.default_page);
                    self.width(data.chart ? data.chart.size_x : 20);
                    self.height(data.chart ? data.chart.size_y : 20);
                    const cells = data.chart ? data.chart.cells : null;
                    self.initChart(false, cells);
                }
            })
            .catch(err => console.error("Error loading project:", err));
    };

    self.incrementRow = () => {
        if (self.rowCount() < 999) self.rowCount(self.rowCount() + 1);
    }
    self.decrementRow = () => {
        if (self.rowCount() > 0) self.rowCount(self.rowCount() - 1);
    };

    self.loadProject();
    
    // send cookies to server when changing stitch shape
    self.stitchShape.subscribe(val => {
        formData = new FormData();
        formData.append('stitch_shape', val);
        fetch(`${baseUrl}projects/preference/`, {
            method: 'POST',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
    });

    // keep them in sync
    self.modeToggle.subscribe(val => {
        self.mode(val ? "custom" : "screenshot");
    });

    self.mode.subscribe(val => {
        self.modeToggle(val === "custom");
        formData = new FormData();
        formData.append('default_page', val);
        fetch(`${baseUrl}projects/preference/`, {
            method: 'POST',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
    });

    self.modeLabel = ko.computed(() => {
        return self.mode() === "screenshot" ? "スクショ" : "カスタム";
    });

    // --- Custom chart ---
    

    // 2D array for pixels (stores hex color or empty string)

    self.currentColor = ko.observable('#000000');

    // Initialize chart
    self.initChart = function(keep=true, cells=null) {
        let rows = [];
        if (self.width() > self.maxSize) self.width(self.maxSize);
        if (self.height() > self.maxSize) self.height(self.maxSize);
        if (self.width() < 1) self.width(1);
        if (self.height() < 1) self.height(1);

        for (let y = 0; y < self.height(); y++) {
            let row = [];
            for (let x = 0; x < self.width(); x++) {
                // keep current color if possible
                if (cells) {
                    const cell = cells.find(c => c.x === x && c.y === y);
                    row.push(cell ? cell.color : '');
                    continue;
                }

                if (keep && self.chart()[y] && self.chart()[y][x]) {
                    row.push(self.chart()[y][x]);
                    continue;
                }
                row.push('');
            }
            rows.push(row);
        }
        self.chart(rows);
    };

    // Paint a pixel
    self.paintPixel = function(rowIndex, colIndex) {
        let rows = self.chart().map(r => r.slice());
        rows[rowIndex][colIndex] = self.currentColor();
        self.chart(rows);
    };    

    // Clear chart
    self.clearChart = function() {
        self.initChart(false);
    };

    // Save chart (you can send as JSON to backend)
    self.saveChart = function() {
        formData = new FormData();
        formData.append('width', self.width());
        formData.append('height', self.height());

        var cells = [];
        for (let y = 0; y < self.height(); y++) {
            for (let x = 0; x < self.width(); x++) {
                if (!self.chart()[y][x]) continue;
                cells.push({
                    x: x,
                    y: y,
                    color: self.chart()[y][x]
                });
            }
        }

        formData.append('cells', JSON.stringify(cells));

        fetch(`${baseUrl}projects/chart/${projectId}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        }).then(r => r.json())
        .then(data => {
            console.log("Saved!", data);
        });
    };

    // Initialize default
    self.initChart();
}

document.addEventListener("DOMContentLoaded", function () {
    const projectId = document.body.dataset.projectId;
    ko.applyBindings(new ColorworkViewModel(projectId));
});