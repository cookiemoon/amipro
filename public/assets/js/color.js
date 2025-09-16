function ColorworkViewModel(projectId) {
    const self = this;
    const baseUrl = document.body.dataset.baseUrl;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function updateToken(newToken) {
        csrfToken = newToken;
        document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
    }

    // Project details
    self.project = ko.observable({});
    self.rowCount = ko.observable(0);
    self.modeToggle = ko.observable(false);
    self.stitchShape = ko.observable('square');
    self.mode = ko.observable('screenshot');
    self.width = ko.observable(0);
    self.height = ko.observable(0);
    self.maxSize = 50;
    self.chart = ko.observableArray([]);

    // Load project details
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
                    self.initChart(cells);
                    self.rowCount(data.project.row_counter || 0);
                }
            })
            .catch(err => console.error("Error loading project:", err));
    };

    // Row Counter
    self.incrementRow = () => {
        if (self.rowCount() < 999) self.rowCount(self.rowCount() + 1);
    }
    self.decrementRow = () => {
        if (self.rowCount() > 0) self.rowCount(self.rowCount() - 1);
    };

    self.loadProject();
    
    // Send cookies to server when changing stitch shape
    self.stitchShape.subscribe(val => {
        formData = new FormData();
        formData.append('stitch_shape', val);
        formData.append('fuel_csrf_token', csrfToken);
        fetch(`${baseUrl}projects/preference/`, {
            method: 'POST',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        }).then(r => r.json())
        .then(data => {
            if (data.new_csrf_token) {
                updateToken(data.new_csrf_token);
            }
        });
    });

    self.modeToggle.subscribe(val => {
        self.mode(val ? "custom" : "screenshot");
    });

    self.mode.subscribe(val => {
        self.modeToggle(val === "custom");
        formData = new FormData();
        formData.append('default_page', val);
        formData.append('fuel_csrf_token', csrfToken);
        fetch(`${baseUrl}projects/preference/`, {
            method: 'POST',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        }).then(r => r.json())
        .then(data => {
            if (data.new_csrf_token) {
                updateToken(data.new_csrf_token);
            }
        });
    });

    self.modeLabel = ko.computed(() => {
        return self.mode() === "screenshot" ? "スクショ" : "カスタム";
    });

    // --- Custom chart ---
    self.currentColor = ko.observable('#000000');

    // Initialize chart
    self.initChart = function(cell_chart=null) {
        let rows = [];
        if (self.width() > self.maxSize) self.width(self.maxSize);
        if (self.height() > self.maxSize) self.height(self.maxSize);
        if (self.width() < 1) self.width(1);
        if (self.height() < 1) self.height(1);

        for (let y = 0; y < self.height(); y++) {
            let row = [];
            for (let x = 0; x < self.width(); x++) {
                if (cell_chart) {
                    const cell = cell_chart.find(c => c.x === x && c.y === y);
                    const color = cell ? cell.color : '';
                    row.push(ko.observable(color));
                    continue;
                }
                row.push(ko.observable('#FFFFFF'));
            }
            rows.push(row);
        }
        self.chart(rows);
    };

    self.updateChart = function(keep=true) {
        let newChart = [];
        if (self.width() > self.maxSize) self.width(self.maxSize);
        if (self.height() > self.maxSize) self.height(self.maxSize);
        if (self.width() < 1) self.width(1);
        if (self.height() < 1) self.height(1);

        for (let y = 0; y < self.height(); y++) {
            let row = [];
            for (let x = 0; x < self.width(); x++) {
                if (keep && self.chart()[y] && self.chart()[y][x]) {
                    row.push(self.chart()[y][x]);
                } else {
                    row.push(ko.observable('#FFFFFF'));
                }
            }
            newChart.push(row);
        }
        self.chart(newChart);
    }

    self.paintPixel = function(rowIndex, colIndex) {
        self.chart()[rowIndex][colIndex](self.currentColor());
    };    

    self.clearChart = function() {
        self.updateChart(false);
    };

    self.saveChart = function() {
        formData = new FormData();
        formData.append('width', self.width());
        formData.append('height', self.height());

        var cells = [];
        for (let y = 0; y < self.height(); y++) {
            for (let x = 0; x < self.width(); x++) {
                if (!self.chart()[y][x]) continue;
                if (self.chart()[y][x]() === '#FFFFFF') continue;
                cells.push({
                    x: x,
                    y: y,
                    color: self.chart()[y][x]()
                });
            }
        }

        console.log(cells);

        formData.append('cells', JSON.stringify(cells));
        formData.append('fuel_csrf_token', csrfToken);

        fetch(`${baseUrl}projects/chart/${projectId}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        }).then(r => r.json())
        .then(data => {
            if (data.new_csrf_token) {
                updateToken(data.new_csrf_token);
            }
            if (data.success) {
                alert("チャート保存しました。");
            } else {
                alert("エラーが発生しました。");
            }
        });
    };

    self.saveRow = function() {
        formData = new FormData();
        formData.append('row_count', self.rowCount());
        formData.append('fuel_csrf_token', csrfToken);

        console.log(csrfToken);

        fetch(`${baseUrl}projects/rows/${projectId}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        }).then(r => r.json())
        .then(data => {
            if (data.new_csrf_token) {
                updateToken(data.new_csrf_token);
            }
            if (data.success) {
                alert("段数カウンター保存しました。");
            } else {
                alert("エラーが発生しました。");
            }
        });
    };

    self.initChart();
}

document.addEventListener("DOMContentLoaded", function () {
    const projectId = document.querySelector('meta[name="project-id"]').getAttribute('content');
    ko.applyBindings(new ColorworkViewModel(projectId));
});