<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Tiara Dewata Penjualan</title>
       
<!-- Font Awesome 5 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/fontawesome.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" />

  <!-- Bootstrap -->

  <!-- Date Picker -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  
  <!-- Data Table -->
  <link rel="stylesheet" type="text/css" href="{{url('https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}"> 
  
  <!-- Data Table Button -->
  <link rel="stylesheet" href="{{url('https://cdn.datatables.net/buttons/1.7.0/css/buttons.dataTables.min.css') }}">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <!-- Local Asset -->
  <link rel="stylesheet" href="{{asset('css/app.css')}}">
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>


<!-- Date Picker -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


  @yield('styles')

  {{-- code lama --}}
  {{-- <script>
          var tabCount = 0; // To uniquely identify each tab and iframe

        function addTab(title, url) {

            tabCount++;

            // Create unique IDs for the tab and its content
            const tabId = `tab-${tabCount}`;
            const iframeId = `iframe-${tabCount}`;

            // Add the tab header with the link
            $("#tabs ul").append(`
                <li id="tab-header-${tabCount}">
                    <a href="#${tabId}">${title}</a>
                    <span class="ui-icon ui-icon-close" role="button" onclick="closeTab(${tabCount})"></span>
                </li>
            `);

            // Add the tab content with an iframe and set src to the specified URL

            // untuk mengatur besarnya kotak, ubah height.nya

            $("#tabs").append(`<div id="${tabId}" class="tab-content"><iframe id="${iframeId}" src="${url}" width="100%" 
            height="600px" 
            frameborder="0"></iframe></div>`);

            // batas


        

            // Refresh the tabs to recognize new tab added dynamically
            $("#tabs").tabs("refresh");

            // Activate the new tab
            $("#tabs").tabs("option", "active", tabCount - 1);
            $('.mega-menu').hide();
            }
          
          
          

        function closeTab(tabIndex) {
            // Remove the tab header and content
            $(`#tab-header-${tabIndex}`).remove();
            $(`#tab-${tabIndex}`).remove();

            // Refresh the tabs to reflect the changes
            $("#tabs").tabs("refresh");

            // Activate the previous tab if any
            const activeTabs = $("#tabs ul li").length;
            if (activeTabs > 0) {
                // Activate the previous tab if any
                $("#tabs").tabs("option", "active", activeTabs - 1);
            } else {
                // If no tabs are left, reload the page
                location.reload();
            }
        }
          
  </script> --}}


{{-- code baru --}}
    <script>
        var tabCount = 0; // To uniquely identify each tab and iframe
        var iframeHistories = {}; // Object to store navigation histories for each iframe
        var openTabs = {}; // To track open iframes by their URL

        function addTab(title, url) {
            // Check if an iframe with the same URL already exists
            const existingTabId = Object.keys(openTabs).find(tabId => openTabs[tabId] === url);

            if (existingTabId) {
                // Focus on the existing tab
                $("#tabs").tabs("option", "active", parseInt(existingTabId.split('-')[1]) - 1);
                $('.mega-menu').hide();
                return;
            }

            // Limit the number of tabs to 5
            if (Object.keys(openTabs).length >= 10) {
                alert("You can only open up to 10 tabs.");
                $('.mega-menu').hide();
                return;
            }

            tabCount++;

            const tabId = `tab-${tabCount}`;
            const iframeId = `iframe-${tabCount}`;

            // Initialize history for the iframe
            iframeHistories[iframeId] = [url];
            openTabs[tabId] = url; // Track the open tab by its URL

            // Add the tab header with the link
            $("#tabs ul").append(`
                <li id="tab-header-${tabCount}">
                    <a href="#${tabId}">${title}</a>
                    <span class="ui-icon ui-icon-close" role="button" onclick="closeTab(${tabCount})"></span>
                </li>
            `);

            

            // Add the tab content with an iframe (untuk atur besar kotak putih, ubah di hightnya)
            $("#tabs").append(`
                <div id="${tabId}" data-id = "${tabCount}" class="tab-content">
                    <iframe id="${iframeId}" src="${url}" width="100%" height="700px" frameborder="0"></iframe>
                </div>
            `);
            console.log(openTabs);
            
            let tabIndex = Object.keys(openTabs).indexOf(tabId);

            // Refresh the tabs to recognize new tab added dynamically
            $("#tabs").tabs("refresh");
            // $("#tabs").tabs("option", "active", tabCount - 1);
            $("#tabs").tabs("option", "active", tabIndex);
            $('.mega-menu').hide();

            // Inject the "Back" and "Refresh" buttons inside the iframe after it loads
            $(`#${iframeId}`).on('load', function() {
                const iframe = this;

                try {
                    const doc = iframe.contentDocument || iframe.contentWindow.document;

                    // Create a button container if it doesn't exist
                    if (!doc.getElementById('button-container')) {
                        const buttonContainer = doc.createElement('div');
                        buttonContainer.id = 'button-container';
                        buttonContainer.style.position = 'relative';
                        buttonContainer.style.display = 'flex';
                        buttonContainer.style.justifyContent = 'start';
                        buttonContainer.style.padding = '10px';
                        buttonContainer.style.backgroundColor = '#f8f9fa';
                        buttonContainer.style.borderBottom = '1px solid #ccc';
                        buttonContainer.style.zIndex = '1000';

                        // Create the Back button
                        const backButton = doc.createElement('button');
                        backButton.id = 'iframe-back-button';
                        backButton.innerText = 'Back';
                        backButton.style.marginRight = '10px';
                        backButton.style.padding = '10px 20px';
                        backButton.style.backgroundColor = '#6c757d';
                        backButton.style.color = '#fff';
                        backButton.style.border = 'none';
                        backButton.style.cursor = 'pointer';
                        backButton.onclick = function() {
                            const history = iframeHistories[iframeId];
                            if (history && history.length > 1) {
                                // Remove the current URL and get the previous URL
                                history.pop();
                                const previousUrl = history[history.length - 1];
                                iframe.src = previousUrl; // Navigate to the previous URL
                            } else {
                                alert("No previous page in this iframe's history.");
                            }
                        };

                        // Create the Refresh button
                        const refreshButton = doc.createElement('button');
                        refreshButton.id = 'iframe-refresh-button';
                        refreshButton.innerText = 'Refresh';
                        refreshButton.style.padding = '10px 20px';
                        refreshButton.style.backgroundColor = '#28a745';
                        refreshButton.style.color = '#fff';
                        refreshButton.style.border = 'none';
                        refreshButton.style.cursor = 'pointer';
                        refreshButton.onclick = function() {
                            iframe.contentWindow.location.reload();
                        };

                        // Add buttons to the container
                        buttonContainer.appendChild(backButton);
                        buttonContainer.appendChild(refreshButton);

                        // Prepend the container to the body of the iframe content
                        doc.body.insertBefore(buttonContainer, doc.body.firstChild);
                    }

                    // Add current URL to iframe's history stack if it's not already the last entry
                    const currentUrl = iframe.contentWindow.location.href;
                    const history = iframeHistories[iframeId];
                    if (history[history.length - 1] !== currentUrl) {
                        history.push(currentUrl);
                    }
                } catch (error) {
                    console.warn('Unable to inject buttons inside the iframe due to cross-origin restrictions.');
                }
            });
        }

        function closeTab(tabIndex) {
            const tabId = `tab-${tabIndex}`;
            const iframeId = `iframe-${tabIndex}`;

            // Remove history and openTabs entry for the iframe
            delete iframeHistories[iframeId];
            delete openTabs[tabId];

            $(`#tab-header-${tabIndex}`).remove();
            $(`#tab-${tabIndex}`).remove();

            // Refresh tabs
            $("#tabs").tabs("refresh");

            const activeTabs = $("#tabs ul li`").length;
            if (activeTabs > 0) {
                $("#tabs").tabs("option", "active", activeTabs - 1);
            } else {
                location.reload();
            }
        }

        // $(document).ready(function() {
        //     $("#tabs").tabs();
        //     addTab("Home", "{{ Url('/sup') }}");
        // });
    </script>

{{-- batas --}}


</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar ( Menu Atas ) -->
  @include('layouts.navigation')

  <!-- Sidebar ( Menu Samping ) -->
  @include('layouts.sidebar')

    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
      <div class="col-md-12">
    <div id="tabs">
    <ul></ul>
    <!-- </div> -->
  </div>
  </div>
  </div>
</div>
  
  @yield('content3')
</div>
  
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-light">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- Local Asset -->
<script src="{{asset('js/app.js')}}"></script>

<!-- jQuery -->

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.bootstrap4.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.print.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.25/dataRender/datetime.js"></script>
 <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
@yield('javascripts')
@yield('footer-scripts')
<script>


$(document).ready(function () {
  // Initialize the jQuery UI Tabs widget
  $("#tabs").tabs();

  // tab saat pertama kali login, akan mengarah ke sini
  addTab("Home", "{{ Url('/orderk') }}");
 
  // Function to add a new tab
  

});

$(document).ready(function() {
  $('.vertical-menu .nav-link').on('click', function(event) {
    event.preventDefault(); // Prevent default action, if needed
	
    var megaMenu = $(this).next('.mega-menu');
    
    // Toggle the mega menu visibility
    $('.mega-menu').not(megaMenu).hide(); // Hide other mega menus if any
    megaMenu.toggle();
  });

  // Optional: Hide the mega menu when clicking outside
  $(document).on('click', function(event) {
    if (!$(event.target).closest('.vertical-menu').length) {
      $('.mega-menu').hide();
    }
  });
});

</script>
</body>

</html>
