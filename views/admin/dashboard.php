<?php
$pageTitle = 'Dashboard';
$pageSubcopy = 'Overview of your store performance';
require __DIR__ . '/_head.php';
?>

<div class="adm-section">
    <!-- KPI Row -->
    <div class="adm-kpi-row">
        <!-- Revenue Demo -->
        <div class="adm-kpi-card">
            <div class="adm-kpi-chip">
                <!-- Dollar sign -->
                <svg viewBox="0 0 24 24">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
            </div>
            <p class="adm-kpi-label">Revenue</p>
            <p class="adm-kpi-value">$24,200.00</p>
            <div class="adm-kpi-delta">
                <span class="adm-delta-up">
                    <svg viewBox="0 0 24 24"><path d="M7 17L17 7M7 7h10v10"/></svg>
                    12%
                </span>
                <span class="adm-delta-vs">vs last month</span>
            </div>
        </div>

        <!-- Orders Demo -->
        <div class="adm-kpi-card">
            <div class="adm-kpi-chip">
                <!-- Package -->
                <svg viewBox="0 0 24 24">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <p class="adm-kpi-label">Orders</p>
            <p class="adm-kpi-value">142</p>
            <div class="adm-kpi-delta">
                <span class="adm-delta-up">
                    <svg viewBox="0 0 24 24"><path d="M7 17L17 7M7 7h10v10"/></svg>
                    5%
                </span>
                <span class="adm-delta-vs">vs last month</span>
            </div>
        </div>

        <!-- Total Products (Live) -->
        <div class="adm-kpi-card">
            <div class="adm-kpi-chip">
                <!-- Tag -->
                <svg viewBox="0 0 24 24">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                    <circle cx="7" cy="7" r="1.5"/>
                </svg>
            </div>
            <p class="adm-kpi-label">Total Products</p>
            <p class="adm-kpi-value"><?= $escape($productCount ?? 0) ?></p>
            <div class="adm-kpi-delta">
                <span class="adm-delta-vs">In catalog</span>
            </div>
        </div>

        <!-- Active Listings (Live) -->
        <div class="adm-kpi-card">
            <div class="adm-kpi-chip">
                <!-- Eye -->
                <svg viewBox="0 0 24 24">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>
            <p class="adm-kpi-label">Active Listings</p>
            <p class="adm-kpi-value"><?= $escape($activeCount ?? 0) ?></p>
            <div class="adm-kpi-delta">
                <span class="adm-delta-vs">Visible in store</span>
            </div>
        </div>
    </div>

    <!-- Mid Row: Chart + Side Panel -->
    <div class="adm-mid-row">
        
        <!-- Revenue Chart -->
        <div class="adm-card">
            <div class="adm-card-header">
                <div>
                    <h2 class="adm-section-title">Gross revenue</h2>
                    <p class="adm-section-sub">Last 8 months</p>
                </div>
            </div>
            <div class="adm-chart-wrap">
                <svg class="adm-chart-svg" viewBox="0 0 600 200" preserveAspectRatio="none">
                    <!-- Gradient definition -->
                    <defs>
                        <linearGradient id="areaGradient" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#a9663d" stop-opacity="0.28"/>
                            <stop offset="100%" stop-color="#a9663d" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    
                    <!-- Gridlines -->
                    <g stroke="#e4dccc" stroke-dasharray="4,4" stroke-width="1">
                        <line x1="0" y1="50" x2="600" y2="50" />
                        <line x1="0" y1="100" x2="600" y2="100" />
                        <line x1="0" y1="150" x2="600" y2="150" />
                    </g>
                    
                    <!-- Area fill -->
                    <path d="M0 160 L 85 140 L 170 145 L 255 100 L 340 120 L 425 70 L 510 90 L 600 40 L 600 200 L 0 200 Z" fill="url(#areaGradient)" />
                    
                    <!-- Line -->
                    <path d="M0 160 L 85 140 L 170 145 L 255 100 L 340 120 L 425 70 L 510 90 L 600 40" fill="none" stroke="#a9663d" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                    
                    <!-- Points -->
                    <circle cx="85" cy="140" r="3" fill="#fcfaf6" stroke="#a9663d" stroke-width="2"/>
                    <circle cx="170" cy="145" r="3" fill="#fcfaf6" stroke="#a9663d" stroke-width="2"/>
                    <circle cx="255" cy="100" r="3" fill="#fcfaf6" stroke="#a9663d" stroke-width="2"/>
                    <circle cx="340" cy="120" r="3" fill="#fcfaf6" stroke="#a9663d" stroke-width="2"/>
                    <circle cx="425" cy="70" r="3" fill="#fcfaf6" stroke="#a9663d" stroke-width="2"/>
                    <circle cx="510" cy="90" r="3" fill="#fcfaf6" stroke="#a9663d" stroke-width="2"/>
                    <!-- Current point -->
                    <circle cx="600" cy="40" r="4" fill="#a9663d"/>
                    
                    <!-- Current callout pill -->
                    <g transform="translate(545, 12)">
                        <rect width="55" height="20" rx="10" fill="#2a2621" />
                        <text x="27.5" y="14" fill="#fcfaf6" font-family="'Inter', sans-serif" font-size="10.5" font-weight="600" text-anchor="middle">$5,240</text>
                    </g>
                </svg>
                <!-- X-axis Labels -->
                <div style="display: flex; justify-content: space-between; margin-top: 1rem; color: #7a7266; font-size: 11.5px; padding: 0 5px;">
                    <span>Jan</span>
                    <span>Feb</span>
                    <span>Mar</span>
                    <span>Apr</span>
                    <span>May</span>
                    <span>Jun</span>
                    <span>Jul</span>
                    <span>Aug</span>
                </div>
            </div>
        </div>

        <!-- Top Products Panel -->
        <div class="adm-card">
            <div class="adm-card-header">
                <div>
                    <h2 class="adm-section-title">Top products</h2>
                    <p class="adm-section-sub">By items sold</p>
                </div>
                <a href="<?= $escape($manageProductsUrl ?? '') ?>" class="adm-see-all">See all</a>
            </div>
            
            <div class="adm-side-list">
                <?php if (!empty($recentProducts)): ?>
                    <?php foreach ($recentProducts as $product): ?>
                        <div class="adm-side-list-item">
                            <div class="adm-product-thumb">
                                <?php if ($product->imageUrl !== null): ?>
                                    <img src="<?= $escape($basePath . $product->imageUrl) ?>" alt="">
                                <?php else: ?>
                                    <div class="adm-product-thumb-empty">
                                        <svg viewBox="0 0 24 24"><path d="M4 22h14a2 2 0 002-2V7.5L14.5 2H6a2 2 0 00-2 2v4"/></svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="adm-product-name"><?= $escape($product->name) ?></div>
                                <div class="adm-product-meta"><?= $escape($product->stockQuantity) ?> in stock</div>
                            </div>
                            <div class="adm-product-value">
                                <?= $escape($product->price) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 2rem; text-align: center; color: #7a7266; font-size: 12.5px;">No products found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Table: Recent Orders Demo -->
    <div class="adm-table-card">
        <div class="adm-table-card-header">
            <div>
                <h2 class="adm-section-title">Recent orders</h2>
            </div>
            <a href="#" class="adm-see-all">See all</a>
        </div>
        
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th class="col-r">Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: 500;">#1042</td>
                    <td class="col-muted">Today, 9:41 AM</td>
                    <td>Eleanor Pena</td>
                    <td>2 items</td>
                    <td class="col-r">$145.00</td>
                    <td><span class="adm-chip adm-chip-processing">Processing</span></td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">#1041</td>
                    <td class="col-muted">Today, 8:12 AM</td>
                    <td>Bessie Cooper</td>
                    <td>1 item</td>
                    <td class="col-r">$65.00</td>
                    <td><span class="adm-chip adm-chip-fulfilled">Fulfilled</span></td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">#1040</td>
                    <td class="col-muted">Yesterday, 4:20 PM</td>
                    <td>Albert Flores</td>
                    <td>4 items</td>
                    <td class="col-r">$312.00</td>
                    <td><span class="adm-chip adm-chip-fulfilled">Fulfilled</span></td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">#1039</td>
                    <td class="col-muted">Yesterday, 1:15 PM</td>
                    <td>Dianne Russell</td>
                    <td>1 item</td>
                    <td class="col-r">$85.00</td>
                    <td><span class="adm-chip adm-chip-cancelled">Cancelled</span></td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">#1038</td>
                    <td class="col-muted">Oct 12, 10:45 AM</td>
                    <td>Jerome Bell</td>
                    <td>3 items</td>
                    <td class="col-r">$210.00</td>
                    <td><span class="adm-chip adm-chip-fulfilled">Fulfilled</span></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php require __DIR__ . '/_foot.php'; ?>
