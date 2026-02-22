import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});


[{
    "id": "oc_ce95225f-d725-4d06-931a-4a45e6d67a9a",
    "description": "KIRIM DOKUMEN\/INVOICE",
    "amount": "10000",
    "category": "BEBAN JASA",
    "vendor_id": "internal",
    "quantity": 1,
    "unit": "SET"
},
{
    "description": "ADMIN BANK",
    "amount": "2500",
    "category": "BEBAN ADMIN BANK",
    "vendor_id": "internal",
    "quantity": 1,
    "unit": "TRX",
    "id": "oc_14bb181d-7d1c-4f02-929c-160468ff4059"
},
{
    "description": "MASTER AWB",
    "amount": "50000",
    "category": "BEBAN JASA",
    "vendor_id": 50,
    "quantity": 1,
    "unit": "SET",
    "id": "oc_6d57ed43-df01-4529-baaf-1db870d8adf9"
},
{
    "description": "MATERAI",
    "amount": "10000",
    "category": "BEBAN JASA",
    "vendor_id": 50,
    "quantity": 1,
    "unit": "SET",
    "id": "oc_296ce046-1b51-4ba9-a5fe-8dc6d770f447"
},
{
    "description": "PARKIR",
    "amount": "46500",
    "category": "BEBAN JASA",
    "vendor_id": "internal",
    "quantity": 1, "unit": "SET",
    "id": "oc_f8e06c63-0e8a-4f5c-b740-cd4f5bb4dcb9"
}]
