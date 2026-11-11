require('esbuild').buildSync({
    entryPoints: ['resources/js/finance/index.jsx'],
    outfile: 'public/js/finance-react.js',
    bundle: true,
    minify: true,
    define: {'process.env.NODE_ENV': '"production"'},
    target: ['es2020'],
    legalComments: 'eof',
});
