@extends('admin.layouts.app')
@section('content')
<style>
    .payment-method-option {
        cursor: pointer;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s;
    }
    .payment-method-option:hover {
        background-color: #f8f9fa;
        border-color: #0d6efd;
    }
    .payment-method-option.selected {
        border-color: #0d6efd;
        background-color: #e7f1ff;
        box-shadow: 0 0 0 1px #0d6efd;
    }
    .payment-method-option img {
        height: 30px;
        object-fit: contain;
    }
    
    /* Promotion radio button styles */
    .promotion-radio {
        width: 18px;
        height: 18px;
        cursor: pointer;
        margin: 0;
    }

    label:has(.promotion-radio:checked) {
        background: #e8f5e9 !important;
        border-color: #4caf50 !important;
        border-width: 2px !important;
    }

    label:has(.promotion-radio):hover {
        background: #f5f5f5 !important;
        border-color: #999 !important;
    }
    
    /* Promotion scope selection styles */
    .promotion-scope-radio:checked + label {
        background: #e7f3ff !important;
        border-color: #0d6efd !important;
    }
    
    .promotion-scope-radio + label:hover {
        background: #f0f8ff !important;
        border-color: #0d6efd !important;
    }
    
    .form-check-input.promotion-scope-radio {
        display: none;
    }
    
    /* Responsive styles for promotion section */
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .promotion-scope-radio + label {
            padding: 10px !important;
            font-size: 13px;
        }
        
        .promotion-scope-radio + label .small {
            font-size: 11px;
        }
        
        #promotion_select_label {
            font-size: 12px;
            line-height: 1.4;
            display: block;
            width: 100%;
        }
        
        .promotion-select {
            font-size: 13px !important;
            padding: 8px 35px 8px 12px !important;
        }
        
        #btn_apply_promotion {
            font-size: 13px;
            padding: 8px 12px;
        }
        
        h4, h6 {
            font-size: 1.1rem;
        }
        
        .list-group-item {
            font-size: 14px;
            padding: 0.75rem 0;
        }
    }
    
    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 5px;
            padding-right: 5px;
        }
        
        .card-body {
            padding: 0.75rem;
        }
        
        h4 {
            font-size: 1rem;
        }
        
        h6 {
            font-size: 0.95rem;
        }
    }
    
    /* Prevent overflow */
    .container {
        max-width: 100%;
        overflow-x: hidden;
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .card-body {
        overflow-x: hidden;
        word-wrap: break-word;
        padding: 1.25rem;
    }
    
    .card {
        max-width: 100%;
        overflow: hidden;
    }
    
    .row {
        margin-left: -10px;
        margin-right: -10px;
    }
    
    .row > * {
        padding-left: 10px;
        padding-right: 10px;
    }
    
    /* Promotion select dropdown styles */
    .promotion-select {
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }
    
    .promotion-select:hover {
        border-color: #0d6efd !important;
        background-color: #f8f9fa !important;
    }
    
    .promotion-select:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15) !important;
        outline: none;
    }
    
    .promotion-select option {
        padding: 10px;
        font-size: 14px;
    }
    
    .promotion-select option:checked {
        background: #e7f3ff;
        color: #0d6efd;
    }
    
    /* Promotion code select auto-apply styles */
    .promotion-select-auto {
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }
    
    .promotion-select-auto:hover {
        border-color: #0d6efd !important;
        background-color: #f8f9fa !important;
    }
    
    .promotion-select-auto:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15) !important;
        outline: none;
    }
    
    .promotion-select-auto option {
        padding: 10px;
        font-size: 14px;
    }
    
    .promotion-select-auto option:checked {
        background: #e7f3ff;
        color: #0d6efd;
    }
</style>
    <div class="container-fluid py-3 py-md-5">
        <div class="mb-3">
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
        <div class="row g-3">
            <!-- Cột thông tin và thanh toán -->
            <div class="col-12 col-lg-7 mb-3 mb-lg-0">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h4 class="mb-4">Thông tin của bạn</h4>
                        <form class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fullName">Họ và tên</label>
                                    <input type="text" disabled class="form-control" id="fullName" value="{{ $customer['name'] }}"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone">Số điện thoại</label>
                                    <input type="text" disabled class="form-control" id="phone" value="{{ $customer['phone'] }}"
                                        required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email">Email</label>
                                <input type="email" disabled class="form-control" id="email" value="{{ $customer['email'] }}"
                                    required>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-4">Chọn phương thức thanh toán</h4>

                        <div class="payment-methods-container">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="payment-method-option" data-target="#vnpayForm" data-method="vnpay">
                                        <img src="data:image/png;base64,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"
                                            alt="VNPAY">
                                        <span>VNPAY</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="payment-method-option selected" data-target="#cashForm" data-method="cash">
                                        <img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBwgHBgkIBwgKCgkLDRYPDQwMDRsUFRAWIB0iIiAdHx8kKDQsJCYxJx8fLT0tMTU3Ojo6Iys/RD84QzQ5OjcBCgoKDQwNGg8PGjclHyU3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3N//AABEIAJQBBAMBIgACEQEDEQH/xAAbAAABBQEBAAAAAAAAAAAAAAADAAECBAUGB//EAEIQAAIBAwICCAMFBAcJAQAAAAECAwAEEQUhEjEGEyIyQVFhcYGRoRQjM0KxUmLB0TRyc4Lh8PEVJFNjdJKys8JU/8QAGQEBAAMBAQAAAAAAAAAAAAAAAAIDBAEF/8QAJhEAAgIBBAIBBAMAAAAAAAAAAAECAxEEEiExQVEyEyIzcQVCUv/aAAwDAQACEQMRAD8A9kXvL7irlDMSKCQoyKB1r55mgFJ3296Nbdw+9JIlZQzDJNQk7B4U2HOgJ3PdX+t/A0CPvr70SJusPC+/jvRGiRVLAYIGRQBBVR++3vS61/2jR1jUgEjJPM0A1t3D70rruD3qEh6o8KZFKMmRuFySBvQEE7496tg1narfWum27TzMFKjO5rjpNe1eVDf211bpbN3In3ZvlyJ8udUWXxg8EXNLs7ZtjvR7fufGuHg6V6pGoa90Z3DDOYjk49udXYOm+mk8E6TWr+IeM7VyOprZxWROque6PehR/iL71Rstd0u9xwXsLjn3vGtFWgZeKJlPqDmrlZF9MllMsGqbd4+9P1j57xo4jjIyRk1I6Nb9z41G65LTOxjbhQkLSjPWEh98b70BCL8Vferh5UF40QcSruOVBMr+ZoCLd81Ztvw/jTrGuNxuaE5MbYUnFASufy/GhQ/irU4/vThxnHLNTdFRSyjBHjQBTyNUjzNSEr+LGjiJCASvOgFB+GKhc80+NRkYo3Am1PEOsJEm4HKgIw/irVk934UORFReJBgihCRtiCd6AhSqyIkI7opUBAzFtuEb7U/2fI3Y1DqnXcgbb86L16eJxQEOuKdnGcbUgvXniJxjbFRMTOSyjY71KNhDlX2J3oBFepw3eJ2pdcX7PCBnankbrQAnMHNQETqQxAwNzvQE+o/epjKUyoGcVPr0oRjZjxKBht+dAS4RN2iceFZ+q6lb6RbvNK+SByP+fp41HWdYg0e0d5mAbw8f9T5CuSs1fV7pb++kV+EloYQ3EE9T5tj5Z96y36jb9seyEp44RNY7jWLsXuojEQPFFbt+revp4e/Kc19pVlMVLRiXkVhjLMPfhBx8cUW8f7TY3MWnXUX2jhKIRIDwt/CuXS0vIQsMmnXiMPDqWYE+YI2+NedKT/ZS3g6a31PT75zbRznjYY6qRGQt7cQGfhUZNGtZFwpkT93OR8jWZpmj3Ut1BcXkZgjhfjVCwLu3hnHIfWia10iS1Y29jwyTrsz/AJUPl6n6VzxyczxyRvej8CKZZJrdVH5pl4MfH/CsqF2jmMen3V1Iy/8A5WaRR8xR9L0q61iZbvU3k+zA5HGd39h4D1rpo40x9mtEEcCHtdWMD296rc9vRbRQ7ZekR0CXU2iD3d3JIPAEAH44rYl1ia1h4mZTgYAI5mq/YjXbCoB8hXJ9LLx2SBUkKlyWC+UfgT7n9KjC6zPDPSmq668tcI6OHpvpspC3izW7/mzGcA/5861rbpHpDpxxXsbA7b+HyrkYrSyn0qC9kaWFZIgzAEsAfEAH1zVe20wXKubRwPButj4PqCc/KtsdVYjzFZI9HhvIbpF6mWORWGzIwI+lF6gDfiNeYHStStmzA1yvrFNxj/tbf6VcsrnpLFHxQ3PGAccNyhVj7c6vjrPDR1W+0eh9fwnGAcUgnXdsnHpXDDpRq9mnFqGl5Ubu0bg4HmcZrRs+nFksSm6trmFT+cxkj6Zq2OqrZJWRZ1BHUHI3JpusMvYwAT41RttYs9TMK20mWkVmQEd4DmRV1VaNuNhsKvjJSWUTXJPqNu8abryNuEbVPrkPjQuqc7gDHvUgSCdaOLkT5Uj9wdjni86dHES8L86aT74jg34aAXWGQ8GMZpdRgZDGmVGjYMw2FT65CMA70BETkDHCKVR6lz4D501AWGIKnGD8aqYbcYz8KS44l96u0BCNhwDf50KfdgRuAKHJ+I3vRrbuH3oCFuDx8R8vaoajfLZxqWRm48jC86Pc9xfVv4GsXWdoof6x/Sq7ZOMG0ck8IYa5bY5N7cS/zoeodKIbWzV1hk7TBQQVOOe/PflXnVvp8Sq17qAYQcbdXCi5e4OeQ9PWle3yyXaROqGQqNl7sI5hF9fMnf8Aj50tVY0UfVlg1ZLySfUmvftNncnhKxw3HFEUyRuNiM7c81K3vb22upri606Vkl4d7bhdVC5xyO57R3z5CsXnvUo2aM5jZkP7pxWXe28sp3mlc3GnXtwW+1LbSeEdxbDC/Hs/qaLFDfRjNpPHLGf/AM92VJ/uyAj5GqBvpyvDK4lXylQNn571FJLTj4jZCNvO3kKfTcfSm47uL2ralf2encFw7JJcZVEeIK6qMZbKsQeYHhXJNITgDAFdBcw2V7wdbdyAoMKJ0Jx/eXP6UBej/WSKsNxG4Y47Dhv8fpUt0SUU5ySRp9GtS1W/Ihbq2hQcJkZe0PQEf55V1kMaxJ1cYPufE1X0yxjsLRIIl4cDBxQta1NNPtGc5L42C86zTlvfB7lcFVDa3+wWo6lZRyiC6uo4kUcTAntP6AczXHahO+r6tJJCvCJCFRW/KoGAT5DmfjVaG01DWb15IoTI7HtM2QieQz4Yrei0xrRltYEeSVzh5ipAY+nko+Zq5V4R5moud0seEXLV55ZIbWxkaKGGMKG/dH5j6nyrQ1DU7bTVSNlklmcdmKMDib1PgB61CC4sdN/3eSRgw70jRtwk++MVn6np51G7GoaXcQXDdWI5IjKBkDJBU+e551YuEUt44Q56RXB3FjDw+X2gg/PgxWtpmowajEzRK6SRnEkb81Ph7iueXSdUY8P2ZY/3pplC/Qk/St3SNNGnQyfedZNKwaRwOEE+AA8BSDfkjHPkPfWsd7byQTdxwQfT/OM1hTTz3s81rc3Ef2CDDXUoj4eRzw/QcsfOr+t3si8NlZb3cxwuD3R4sfaub1m4ighGlWbloYT9+/7b+P8An+VdbEmaHR/WOt6XW1zIpSFUdEjH5F4T4fAV6G+tWjLgCXf90fzrxuG3meGW5RT1cOOI5xjJwPfnW3Y6Dc3Vosz3hhZxlU4S3z3FWV6icFhCNjR6INUtycDrM/1a2QQABkV5d0Wilhu7yC4JLxlFOWz516K2OI+9btNa7E8l8JuS5JzA8ZI3FTt9mYkYB5VOD8MVC6/J8a0kyczdggHc1XAIwB+lShx1q1Z/KTQCBAA3HzpVS2p6AtPGgUkKAQKr9Y/7R5VLrmY8PZ3qf2ceBPxoCaIpUFlBJ8aFMercBOyD5UjKydkY286dV67tN7UBGEl2w54hjO9YvS+6jsbW3kaIspk4Twnfln+FbjKIBxL7b1zXTkmXSFP7DhvqB/GqdR+JkZ/E5OK7IvHuYb2Euy8KpdwsoQeQZT/rWbqNhqV7dtfIkc0xI7UMgK4Gcbc/H401IEDcHHtXib/DMW72UpLq6tiRdWjoRzyuKePUrd8ZPCfHNaiX10iYEzFfJzxD5EGoPJazb3On20pPNkXqz9P5U+05wVVuIGYL10YJ3GT4UUZxmg3OiaHdjPDcWzDkQAw+mP0qvF0WuVmVdK1dXye6XIx8MV1qOOyUIObxEveFWtLbgv4j6iq02m6vZj761aX/AJidr9KqNqqWMiPcxTphgSQhOPpVXy4RppjKq1SkuD0DjZSQGI8K57VLtlvpFnijnUHslsgge4NXdO17S9QTjtr1TnwYFSPgaz9cUfa+McJDAYKnIO2P4VCCcXyenqZxlRJxYOG4tkOYZLq03z2H41B+hq5BfXSkC31C3m8llyh/h+prFpeHpV+9nhKTSNYxqgZ5dPnhJ3MlrKcfIYH61ULWM77X6h+XDe24JH98cJ+VVo5XjOY3dCORViKP9vndcT9VOPKWMPn+P1ru5Hdxpwhba3jcalLFK7cKJbSmVGPhgOCc1dvdTksrYSyWk3ByLgrt777fKucH2EuGNm0LA5D20pUg+eDkVcF3b8X2i7vrm5SEcaW8iAcTDlkjY1NSJqWUCuZ5dNtXnm21O9XPL8CP08c1hW0El1cRwQrxSyEKBn6mpXlzLeXMs9w3FK5z7eg9Kv8AQjUYH1+eyWNC/UM4lzuMEAqPfPP0ollke2dUukwQ6YtmAXj4SspA3fi5keoODj0qkl9dabarBPZyXQjHDHPb91wOXEOamt/1z8qDcIgid+AcYGzAbip49FuPRg9F55Lq9v55cB3ZCQPDnXqHVp+yK8w6Lf0/Uf7b/wCmr0kzsCRttWzRdMnV0NKxRyqEgbbCpQjjLcfaxyzTqglHG1M33Jyv5vOtxaSlRUjLIACPEUAu/wC0edEDmXsNgA+VSMC45nagCCNMDsj5UqB9oYfs0qAfqWXtEjap9euPHNTd1KkAjlVXgbnwnlQBOqZu0p2PnTqep7Lbk0RGUIASAaFN2nBXcelAOzdcOBfrWP0igDW8cUqhkckEfKteHsPlthjxrN6REFIMEczVOo/GyMujzzQ7GPUUk6x3QibgDKOQ4SeXwqj0ikt9AuYobiSR+sHErCMgf41sdEe5L/1R/wDW1UNS4jcT20h6yJZD93IOJfkdvpXjvGOTI+jLh1KymxwXMeTsATire2AQcg+IrOm0LTJmJa2K/wBm5U/xpouj9pE2bfU7+1PhgBx+orm2D8lZpeOKuaQwW/jOeZxWIbDW7cH7NqFlfp+zMvVv8OVRTVr3TZEl1PSLmEA/iJ20/wC7l9a5KptcGjTT2Wxkz0ZSy91iPjXOajdSLdzRzxQzpxnAlTJHxGDRbPpdol2creCNvKVSo+dVtXeKW7MsEiSowB4kYMOQ8qojGUZdHq6qcLKHtZn3FjpN4cy2k1q/7VrKMfJhVc6Jg5sNbxvstzGVPzGatZzTfT3q7c/J4eSo9pr1uSRbLdKPzQsH+nOqZ1xIH4L2CS3fPKRSp+tbKkg5UlT5g0cX10EKNL1iHmsqiQH4Nmu5i+xwZUWp2UhQJOvE+wB/nVxWV+6Q3tvQp9P0m5PFLpcMUn/EtHaEj+6CVJ+FCj0nToHa4lu72fqwSkMuNz5FgabYvoFvy9eVQnGYJPQVTNzNxuePdiSdhj/Cqja5wSlTErx8sqTk+HKmxjA1y7lktoWAnm2DE44B4sfQV0ujxx6DppnEgnhTKWZeIBpWPebOMhefwFVdI0siR1uyUYAPfS5x1SDcRA+B8z5+1V9VvzfXHEqmOGMcEMY24VHpVmcE1wd7p0xuNPtpWOWaNSTjG/j9aJcfgP7VR6OgjRLQEEdknB9zV64/Af2qXgtXRgdFf6fqB/5//wBNXpJhcknIrzbop/TL/wDtx/5GvT+Nf2hW3RfFk6ugauIxwMOXlTNmc9jbHPNRlBZyVGRtyqUB4Gbi2z51tLRhG0ZDMdh5VPr1IIwRTysGQqpyTQOFj+U86AmYHJzkUqOHXHeFNQFRO+OVXdhUHRQpIUcvKq3E2+WPKgE4HGfHejW2ApqcaqUBIB2oU/YccO23hQErn8PHjnxrF1vAjh92/QVrwZZ8OeLbxrN6RABYABzLfoKpv/GyMujh+iXcl/6k/wDrahdIbZ7e4nvZAq2pIJk4hhfDfy3qvoGpwWQeOYuHMvWKVXiGOEr/ABpvttvrSi01CQCZGIhmY5jl32DL4ZHjXkcNGX+pUjdZVDROrqeRVgc0+2d6HNoEU+oNbu3+ypQcoqkskhz4Hy9KnPoeu2YzGouo/AxvxH5Hf5ZqDjxwQwxz67/GpRyPGcxu0fqjEGsW71e40+VUu7F1Ujc4KkH2P60W316xncIHZWPgVJ/SubJY4Ocl+e3tbli11aW0zHmzRAMf7wwa0bToppctqjwddbM2SSj5H13+tZ6SxybI6sfIHeuo0U5sQDzViMH4H+NQslJLs9H+PhGe5SRzmoaHqFivFb6jHKpOAJwR9d/pWaW1u33m00XKD89o4f6Df6V2utrx2DEc1INcz5Hx967XPK5RHXUxqa2rsy49esi5SbrIHzgiRdx71fhure4P3M8bnyDUeVjMnBPwzL5TKH+p5Vm3GiWE2SsPVH9wnHyqb2swGjUJ1JhfbwrJGj3seBZ6rLCvgHBZflyq1Baa8GKcel3akYDCUxMfgRzrqrT6YM7ULkRp1YPabc+gq90d0yWMw3nV8V1Kf9zQjkf+KfQeHtmgWPRu6S7M/SS2n6kNxERqXWTyAI2Arp3ePR7RrlIzHd3QIt42OTbxeAHltj2qzomirq88dpbrpNm3EiHNxJzMj+VB0DTDqN5h1It4u1KfPyUe/wClZTOIo2d2wqgkk10Oj6nJFYRJpTWcySLx4cFHcn0JGfLY1HvkLs7MAKAqgBQMDA2FDuPwW/z41k2d5rDXEf2yxjS2z95IGxwjz5mj6hq1pbfdTypGxI7JOXx/VHL41PPBbngz+iX9Lvv7cf8Aka9EYdo8udeddECGub1hyaVSPbJr1DgX9kfKtuj+LJ1dEYPwhyzULnfhqMxKyEKcCpQDiZ+Lf3raWg4R94MVaPIioTKBGSowfSq5ZuXEedARO5Ow+dPVwIuO6PlTUADrnbY4wTjlReoT1+dD6kqeIsMDflUvtAzyoCBlZCVXGBUkUTZZ+Y22puqL5bIGacHqQV5k70A7qIQGTmTjesfXmLrDxY24v0Fa5brgB3fGszWbWZ1i6pGcDizwjlyqq9Zg0iMujieifUGyl6vh67jbrMc8Z2+FYPSFFTWLnhIIZg2AcgZH+tdEvQuYEsktwreJAGf1pj0HmOTx3BycnsDf615X05+jPtl0YdlqccsC2OqhpbcH7uUd+I+lbdvqE+mdWt5J19lJ+FdLuDnkGx4/586w9b0G90eQG4if7O+ySkePkfI0HTNUksA0TKJrVx95A3Ig+I8jXMOL5I8p4Z3zGC5g+8EcsLYBD4K7/Sub1Po7BdyyLBZrZIvKVZd5P6q8h7nFQgjiiVLu2ea701c9Zbo+WhPkRncenx3proLNJFcW0Z0uAA9sDgebOOSDnjzPnRs62cvqHRc2YMjXqLI3dgJzK3uw/gPnUUl6R6IqkGe2hG6xbSkjzI3I+ldEk8Vtk2MbLI25uJN5D655D4b1WLEsWJJJOSSdzUJTT7IqTi8xZmHp1dvE0F1bQSA/mU8DfKhwdILRl+9SSM/A0fW7YXFjIwXMqDiU43PpWLZaHcz4a4VrZCM9sYc/3fD41xKOMnZ22WJbnk6GC+tJyBFcxsT4ZwfrVkg43FYV/oI63j01yikDsSNkg+/rzqqYtXsAHe2uDF4yRAunxK5ApsT6Kzp/hTGuZh1y5B2mhfP5ZRw/WrsfSCNf6VbSR/voeNf50db8A3YZpYN4JXjP7jEVQ1GaWe7eSdy7nGWPlj/Wmi1WwkTiFygX9p9l+fKh37MJEEIMkkmBGEHEWPhjHOuLKfITKN2hu2W0jXjaQ44R4+PyGMk+AGTyrp9OX7Po8f8AtF1msLdyYU4cG5l9M/lz8/pVfSNKhjSVp5FWCMZvJwcg756pD478yOZ5eFVtT1Br+YPw9XboOGKIckX+dT8E+jXs9XnOn313JIGnEgMYJ5EgBcDyG5rc0y3tzp8QSNJVlUM7MMlyefEawdP6KX13aR3DiROsGVHUsTjwOfWrydFdSjjMcdzcpGearC4FSUZ+iSUvQTozHFHqN9HbkGIToFx5V6IZnBI2+VcRoGiz6W7cQlcO6tnqiuMV3BgJOeIVv0awnlF9awiSoJRxNnJ8qZ/uCOD83PNIP1K8PMimP3+MbFedbCwSu0pCPjB8qn1CYzv586hwGIhyQQKl148tqAh17+nypU/UHwYfKlQBHkQqQGGaB1T4PCtMveHuKuZoAaSIqgE4OORocuZHBjGRjc0OTvt70a27re9AQiHVtxN2RRWkRlIDAkjAprk9lff+BoEffX3oB+rbPdNHWRVXDHBFEBqpJ3296Aa9t47yN45I1lideF0YZBFeZdKuic2kE3doryWRO/i0Xv6evzr1S37p96jchWThYAqTgg+Iqm2mNiwQlBS7PD7G6nsrgS28hQtscHmD51elkeWQySszOebMcmt/pX0RaJmvtHQsnelthzHqvp6VnaTot/qxH2aEiPk0r9lB8fH2FeVZTNS24Msq5J4M+tbSejmo6p24ouqh/wCLLsD7Dx9+VdZpPRiysOGSb/eJx+dh2V9lro7fZCByzWmvQ55mWwo/0cSmiHSVMjwFmAz1p3+XlXJysWlY53ya9huMFR471kXuhabqD/f2yhz+ePsEfKoz/j8cxZote6tQSweZCpI7ROJInZHHIqcGus1DoNcR8R0+4WUeCS9k/MVzV7p17YNi8tpIvUjY/HlWWdNkO0YXCUQUzwXYI1Cyt7nPN3QB/wDuG9Z8nR3S5e1YXtxYv+xMesjPpnmKt8+VP4ZqKnJEcmNP0a1SBlZmiNrntTwgsvzUH9K19H0SGN1WyvTLcsD9omCEJDH44LAdr12osU0sDcdvI0T+anGaWoanObRoBwBZG+8dVCs/vUt+SSaBarfQzqlnYYWyg7g5dYR+atPoToH+1rz7XcRk2kBBIPKR/Aew5msfSNLn1a/itLfZm3ZyNo0HNj7eA8TXsukWkFhYx2tsgSKMYA8T6n1rVpqd73PotrhueWFi7DEvtnlnxqcjqyFVOTUbn8vxocP4gPhXp4NIurb9g1YEiAAFhkVPwNUjzNdASReNiy9oelSi+7ZjIOHPjU4COrG9Quea/GgJSOrqVU5J8KCI2A7S7Cnh/EFWT3T7UBESpgZYUqq0qAtuBwHlVTJ3GSKIJXJAJyDtyovUpnlQEowOBdhyoM/4gxttTF2RiqnAB8qlGvWqS/PNARgOXx4Y8aPIBwNsOVDkXqlBTYk43qCyMzBS2x2O1ADyfWrSAcAyOdN1KHwPzoRkZSQDsDtQCuNn2yBjwpW4y5zvt41OJetUmTcg00qiIBk55xQBHGEJUCqo2GBtjlgUQSM7BTyJxRepTyPzpwCSAY8KBPs4x9KRlcHAb6VONetXifnnwoCNvvI2fKjSDsHGAaFIOqAKbHOKisjMwVjkGgBjIA7RzVkxq8fC6hgRuCMg0upTOd6CZHBIB29qAxtT6L6XdOT1LQuR3odvpyrm73obfQ5aykjulH5T2H/l9a9BjUSLxNuc1GQCLdNs86z2aaufaK3XFnkNxbz2jlLqGSFh4SKVqldqz8EaAs7HhVVGSSfADxr2aSOO6AiuESRDsVZQQapw9GNLt79L2G34JEzwqGPCp8wPA1lehafDKvocgOh/R9dE08daqm8m7Uzc8H9kH0rXn2fbIHkKbrGU4B5cqJEiyLxNnNehGKisI0JYWCNv3jk7Y8aJMB1TYHyqEoMXD1ZxnnUVdpHCscqfSpHSAJ5dqragcI2HKodSmOR+dB61xsG29qAUxIkI5Cp2+7Nk5p0USpl+dNJ91w8G2c5oAk2BGTjl5VVBIAzmiIzSMFc5FF6pOeOVATUDA5Uqrda/n9KVARXvD3FXKelQFKTvt70e27h96VKgFc91f638DQI/xF96VKgLg5VUfvt701KgLFt3D70113B70qVABT8Qf1quDlSpUBSbmfej23cPvT0qAjc90e9Cj/EX3pqVAXD41TbvH3pUqAPb9z41G67q09KgBRfir71cPKlSoCk3fNWLb8P40qVARuvy/GhQ/irSpUBb8DVI86elQFiD8MVC55r8aVKgIQ/irVk90+1KlQFOlSpUB//Z"
                                            alt="Thanh toán tại quầy">
                                        <span>Thanh toán tại quầy</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="creditCardForm" class="payment-details mt-4" style="display: none;">
                            <h6>Chi tiết thẻ</h6>
                            <div class="row">
                                <div class="col-12 mb-3"><input type="text" class="form-control" placeholder="Tên trên thẻ">
                                </div>
                                <div class="col-12 mb-3"><input type="text" class="form-control" placeholder="Số thẻ"></div>
                                <div class="col-md-6 mb-3"><input type="text" class="form-control"
                                        placeholder="Ngày hết hạn (MM/YY)"></div>
                                <div class="col-md-6 mb-3"><input type="text" class="form-control" placeholder="CVV"></div>
                            </div>
                        </div>
                        <div id="vnpayForm" class="payment-details mt-4 text-center" style="display: none;">
                            <p class="text-muted">Bạn sẽ được chuyển hướng đến cổng thanh toán VNPAY.</p>
                        </div>
                        <div id="cashForm" class="payment-details mt-4">
                            <p class="text-muted">Bạn sẽ thanh toán trực tiếp tại quầy sau khi sử dụng dịch vụ.</p>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Cột tóm tắt đơn hàng -->
            <div class="col-12 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-4">Tóm tắt đơn hàng</h4>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">Đóng</button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="mb-3">
                            <h6 class="mb-3">Khuyến mãi</h6>
                            
                            <!-- Chọn loại khuyến mãi (Tab) -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-2">Chọn loại khuyến mãi:</label>
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <div class="form-check" style="padding-left: 0px !important;">
                                            <input class="form-check-input promotion-scope-radio" 
                                                   type="radio" 
                                                   name="promotion_scope" 
                                                   id="scope_order" 
                                                   value="order"
                                                   {{ $appliedCoupon && $appliedCoupon->apply_scope === 'order' ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                            <label class="form-check-label w-100 border rounded p-2" 
                                                   for="scope_order" 
                                                   style="cursor: pointer; transition: all 0.2s; {{ $appliedCoupon && $appliedCoupon->apply_scope === 'order' ? 'background: #e7f3ff; border-color: #0d6efd !important;' : 'background: #f9f9f9;' }}">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <i class="fa fa-file-text-o"></i>
                                                    <strong class="small">Theo hóa đơn</strong>
                                                </div>
                                                <div class="text-muted small" style="word-wrap: break-word;">Áp dụng trên tổng tiền hóa đơn.</div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-check" style="padding-left: 0px !important;">
                                            <input class="form-check-input promotion-scope-radio" 
                                                   type="radio" 
                                                   name="promotion_scope" 
                                                   id="scope_customer_tier" 
                                                   value="customer_tier"
                                                   {{ $appliedCoupon && $appliedCoupon->apply_scope === 'customer_tier' ? 'checked' : '' }}
                                                   style="cursor: pointer;">
                                            <label class="form-check-label w-100 border rounded p-2" 
                                                   for="scope_customer_tier" 
                                                   style="cursor: pointer; transition: all 0.2s; {{ $appliedCoupon && $appliedCoupon->apply_scope === 'customer_tier' ? 'background: #e7f3ff; border-color: #0d6efd !important;' : 'background: #f9f9f9;' }}">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <strong class="small">Theo hạng khách hàng</strong>
                                                </div>
                                                <div class="text-muted small" style="word-wrap: break-word;">Chỉ áp dụng khi khách đạt hạng tối thiểu.</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Select mã khuyến mãi -->
                            <div class="mb-3" id="promotion_select_container">
                                <label for="promotion_select" class="form-label small fw-semibold mb-2">
                                    <i class="fa fa-tag" style="color: #0d6efd;"></i>
                                    <span id="promotion_select_label" style="word-wrap: break-word; overflow-wrap: break-word;">Chọn mã khuyến mãi áp dụng theo hóa đơn:</span>
                                </label>
                                <div class="row g-2">
                                    <div class="col-12 col-sm-8 col-md-8">
                                        <div class="position-relative">
                                            <select class="form-select promotion-select" id="promotion_select" name="promotion_select" style="padding: 10px 40px 10px 15px; font-size: 14px; border: 2px solid #e0e0e0; border-radius: 8px; transition: all 0.3s ease; background-color: #fff; width: 100%;">
                                                <option value="">-- Chọn mã khuyến mãi --</option>
                                            </select>
                                            <i class="fa fa-chevron-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #666; pointer-events: none; font-size: 12px;"></i>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4 col-md-4">
                                        <button type="button" class="btn btn-primary w-100" id="btn_apply_promotion" style="white-space: nowrap;">
                                        <span class="d-none d-sm-inline">Áp dụng</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Chi tiết mã khuyến mãi đã chọn -->
                            <div id="promotion_details" class="border rounded p-3 mb-2" style="{{ (\Illuminate\Support\Facades\Session::has('coupon_code') && $appliedCoupon) ? 'display: block;' : 'display: none;' }} background: #f9f9f9;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1" id="promotion_name">{{ $appliedCoupon ? $appliedCoupon->name : '-' }}</h6>
                                        <div class="small text-muted" id="promotion_code_display">{{ $appliedCoupon ? 'Mã: ' . $appliedCoupon->code : '-' }}</div>
                                    </div>
                                    <span class="badge {{ $appliedCoupon && $appliedCoupon->apply_scope === 'order' ? 'bg-primary' : ($appliedCoupon ? 'bg-warning text-dark' : 'bg-success') }}" id="promotion_scope_badge" style="color: #fff;">
                                        {{ $appliedCoupon && $appliedCoupon->apply_scope === 'order' ? 'HÓA ĐƠN' : ($appliedCoupon && $appliedCoupon->apply_scope === 'customer_tier' ? 'HẠNG KHÁCH HÀNG' : '-') }}
                                    </span>
                                </div>
                                <div class="small">
                                    <div class="mb-1">
                                        <strong>Loại giảm:</strong> 
                                        <span id="promotion_discount_info">
                                            @if($appliedCoupon)
                                                @if($appliedCoupon->discount_type === 'percent')
                                                    Giảm {{ $appliedCoupon->discount_percent ?? 0 }}%
                                                    @if($appliedCoupon->max_discount_amount)
                                                        (tối đa {{ number_format($appliedCoupon->max_discount_amount, 0, ',', '.') }}đ)
                                                    @endif
                                                @else
                                                    Giảm {{ number_format($appliedCoupon->discount_amount ?? 0, 0, ',', '.') }}đ
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                    <div id="promotion_conditions">
                                        @if($appliedCoupon)
                                            @php
                                                $conditions = [];
                                                if ($appliedCoupon->min_order_amount) {
                                                    $conditions[] = 'Đơn hàng tối thiểu: ' . number_format($appliedCoupon->min_order_amount, 0, ',', '.') . 'đ';
                                                }
                                                if ($appliedCoupon->min_customer_tier) {
                                                    $conditions[] = 'Từ hạng ' . $appliedCoupon->min_customer_tier . ' trở lên';
                                                }
                                                if ($appliedCoupon->per_user_limit) {
                                                    $usageCount = \App\Models\PromotionUsage::where('promotion_id', $appliedCoupon->id)
                                                        ->where('user_id', $appointment->user_id)
                                                        ->count();
                                                    $conditions[] = 'Mỗi khách hàng: ' . $usageCount . '/' . $appliedCoupon->per_user_limit . ' lần';
                                                }
                                                if ($appliedCoupon->usage_limit) {
                                                    $totalUsage = \App\Models\PromotionUsage::where('promotion_id', $appliedCoupon->id)->count();
                                                    $conditions[] = 'Số lượt sử dụng: ' . $totalUsage . ' lượt';
                                                }
                                                if ($appliedCoupon->start_date) {
                                                    $conditions[] = 'Bắt đầu: ' . $appliedCoupon->start_date->format('d/m/Y');
                                                }
                                                if ($appliedCoupon->end_date) {
                                                    $conditions[] = 'Kết thúc: ' . $appliedCoupon->end_date->format('d/m/Y');
                                                }
                                            @endphp
                                            @if(count($conditions) > 0)
                                                <div class="mt-2"><strong>Điều kiện áp dụng:</strong></div>
                                                <ul class="mb-0 mt-1" style="padding-left: 20px;">
                                                    @foreach($conditions as $condition)
                                                        <li>{{ $condition }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                @if(\Illuminate\Support\Facades\Session::has('coupon_code') && $appliedCoupon)
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn_remove_promotion" title="Xóa mã">
                                        <i class="fa fa-times"></i> Xóa mã đã áp dụng
                                    </button>
                                </div>
                                @endif
                            </div>
                            
                            @if(isset($promotionMessage))
                                <small class="text-{{ $appliedCoupon ? 'success' : 'danger' }} d-block mt-2" style="word-wrap: break-word;">{{ $promotionMessage }}</small>
                            @endif

                            <div id="promotion_message" class="mt-2 small" style="display: none; word-wrap: break-word;"></div>
                            <input type="hidden" id="applied_promotion_id" name="applied_promotion_id" value="{{ $appliedCoupon ? $appliedCoupon->id : '' }}">
                            <input type="hidden" id="promotion_discount_amount" name="promotion_discount_amount" value="{{ $promotion }}">
                        </div>

                        <ul class="list-group list-group-flush" style="padding: 15px">
                            @foreach($services as $s)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    {{ $s['name'] }}<span>{{ number_format($s['price']) }}đ</span></li>
                            @endforeach

                            <li id="promotion_discount_item" class="list-group-item d-flex justify-content-between align-items-center px-0 text-success" style="{{ $promotion > 0 ? '' : 'display: none;' }}">
                                Khuyến mãi
                                <span id="promotion_discount_display">-{{ number_format($promotion) }}đ</span>
                            </li>

                            @php
                                // Tính lại nếu chưa có
                                $displayTaxablePrice = $taxablePrice ?? ($subtotal - $promotion);
                                // $displayVAT = $vat ?? ($displayTaxablePrice * 0.1);
                            @endphp

                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Tạm tính</span>
                                <span id="taxable_price_amount">{{ number_format($displayTaxablePrice) }}đ</span>
                            </li>
                            {{-- <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>VAT (10%)</span>
                                <span>{{ number_format($displayVAT) }}đ</span>
                            </li> --}}

                            <li
                                class="list-group-item d-flex justify-content-between align-items-center border-top pt-3 px-0">
                                <strong>Tổng cộng</strong><strong
                                    style="font-size: 1.2rem;" id="total_amount">{{ number_format($total) }}đ</strong></li>
                        </ul>
                        <form style="padding: 15px" action="{{ route('admin.appointments.process-checkout') }}" method="POST" id="paymentForm">
                            @csrf
                            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                            <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="cash"> {{-- Giá trị
                            mặc định --}}
                            <input type="hidden" name="applied_promotion_id" id="form_applied_promotion_id" value="{{ $appliedCoupon ? $appliedCoupon->id : '' }}">
                            <input type="hidden" name="promotion_discount_amount" id="form_promotion_discount_amount" value="{{ $promotion }}">
                            <button class="btn btn-primary btn-lg btn-block mt-4" type="submit">Xác nhận thanh toán</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentOptions = document.querySelectorAll('.payment-method-option');
            const paymentDetails = document.querySelectorAll('.payment-details');
            const selectedPaymentMethodInput = document.getElementById('selectedPaymentMethod');

            // Function to update payment details display and hidden input
            function updatePaymentSelection(selectedOption) {
                paymentOptions.forEach(opt => opt.classList.remove('selected'));
                selectedOption.classList.add('selected');
                const targetId = selectedOption.dataset.target;
                const method = selectedOption.dataset.method;

                paymentDetails.forEach(detail => detail.style.display = 'none');

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.style.display = 'block';
                }

                if (selectedPaymentMethodInput && method) {
                    selectedPaymentMethodInput.value = method;
                }
            }
            console.log(paymentOptions);
            
            // Add click listeners to all payment options
            paymentOptions.forEach(option => {
                option.addEventListener('click', function () {
                    updatePaymentSelection(this);
                });
            });

            // Set initial state based on default value in hidden input or 'cash' if not set
            const initialMethod = selectedPaymentMethodInput ? selectedPaymentMethodInput.value : 'cash';
            const initialOption = document.querySelector(`.payment-method-option[data-method="${initialMethod}"]`);

            if (initialOption) {
                updatePaymentSelection(initialOption);
            } else {
                // Fallback to cash if no initial option found (e.g., if value is not 'cash')
                const cashOption = document.querySelector(`.payment-method-option[data-method="cash"]`);
                if (cashOption) {
                    updatePaymentSelection(cashOption);
                }
            }

            // Promotion data from PHP
            @php
                $orderPromotionsData = $availableOrderPromotions->map(function($promo) use ($appointment) {
                    $remainingUsage = null;
                    if ($promo->per_user_limit && $appointment->user) {
                        $usageCount = \App\Models\PromotionUsage::where('promotion_id', $promo->id)
                            ->where('user_id', $appointment->user->id)
                            ->count();
                        $remainingUsage = max(0, $promo->per_user_limit - $usageCount);
                    }
                    return [
                        'id' => $promo->id,
                        'code' => $promo->code,
                        'name' => $promo->name,
                        'scope' => 'order',
                        'discount_type' => $promo->discount_type,
                        'discount_percent' => $promo->discount_percent ?? 0,
                        'discount_amount' => $promo->discount_amount ?? 0,
                        'max_discount_amount' => $promo->max_discount_amount ?? 0,
                        'min_order_amount' => $promo->min_order_amount ?? 0,
                        'per_user_limit' => $promo->per_user_limit ?? 0,
                        'usage_limit' => $promo->usage_limit ?? null,
                        'start_date' => $promo->start_date ? $promo->start_date->format('d/m/Y') : null,
                        'end_date' => $promo->end_date ? $promo->end_date->format('d/m/Y') : null,
                        'remaining_usage' => $remainingUsage,
                    ];
                });
                
                $customerTierPromotionsData = $availableCustomerTierPromotions->map(function($promo) use ($appointment) {
                    $remainingUsage = null;
                    if ($promo->per_user_limit && $appointment->user) {
                        $usageCount = \App\Models\PromotionUsage::where('promotion_id', $promo->id)
                            ->where('user_id', $appointment->user->id)
                            ->count();
                        $remainingUsage = max(0, $promo->per_user_limit - $usageCount);
                    }
                    return [
                        'id' => $promo->id,
                        'code' => $promo->code,
                        'name' => $promo->name,
                        'scope' => 'customer_tier',
                        'discount_type' => $promo->discount_type,
                        'discount_percent' => $promo->discount_percent ?? 0,
                        'discount_amount' => $promo->discount_amount ?? 0,
                        'max_discount_amount' => $promo->max_discount_amount ?? 0,
                        'min_order_amount' => $promo->min_order_amount ?? 0,
                        'min_customer_tier' => $promo->min_customer_tier ?? null,
                        'per_user_limit' => $promo->per_user_limit ?? 0,
                        'usage_limit' => $promo->usage_limit ?? null,
                        'start_date' => $promo->start_date ? $promo->start_date->format('d/m/Y') : null,
                        'end_date' => $promo->end_date ? $promo->end_date->format('d/m/Y') : null,
                        'remaining_usage' => $remainingUsage,
                    ];
                });
            @endphp
            const orderPromotions = @json($orderPromotionsData);
            const customerTierPromotions = @json($customerTierPromotionsData);
            const subtotal = {{ $subtotal }};
            let selectedPromotion = null;
            let isApplyingPromotion = false;

            // Function to format number with Vietnamese format
            function formatNumber(num) {
                return new Intl.NumberFormat('vi-VN').format(num);
            }

            // Function to update prices on the page
            function updatePrices(discountAmount) {
                // Ensure discountAmount is a valid number
                const promotionDiscount = (discountAmount !== undefined && discountAmount !== null && !isNaN(discountAmount)) 
                    ? parseFloat(discountAmount) 
                    : 0;
                
                const taxablePrice = Math.max(0, subtotal - promotionDiscount);
                const total = taxablePrice;

                // Update promotion discount display
                const promotionItem = document.getElementById('promotion_discount_item');
                const promotionAmount = document.getElementById('promotion_discount_display');
                if (promotionItem && promotionAmount) {
                    if (promotionDiscount > 0) {
                        promotionItem.style.display = '';
                        promotionAmount.textContent = '-' + formatNumber(promotionDiscount) + 'đ';
                    } else {
                        promotionItem.style.display = 'none';
                    }
                }

                // Update taxable price
                const taxablePriceEl = document.getElementById('taxable_price_amount');
                if (taxablePriceEl) {
                    taxablePriceEl.textContent = formatNumber(taxablePrice) + 'đ';
                }

                // Update total
                const totalEl = document.getElementById('total_amount');
                if (totalEl) {
                    totalEl.textContent = formatNumber(total) + 'đ';
                }

                // Update hidden form fields
                const formPromotionId = document.getElementById('form_applied_promotion_id');
                const formDiscountAmount = document.getElementById('form_promotion_discount_amount');
                if (formDiscountAmount) {
                    formDiscountAmount.value = promotionDiscount;
                }
            }

            // Function to populate promotion select dropdown
            function populatePromotionSelect(promotions) {
                const select = document.getElementById('promotion_select');
                if (!select) return;
                
                // Clear existing options except the first one
                select.innerHTML = '<option value="">-- Chọn mã khuyến mãi --</option>';
                
                if (promotions && promotions.length > 0) {
                    promotions.forEach(function(promo) {
                        const option = document.createElement('option');
                        option.value = promo.id;
                        option.textContent = promo.code + ' - ' + promo.name;
                        option.dataset.promotion = JSON.stringify(promo);
                        select.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Không có mã khuyến mãi nào';
                    option.disabled = true;
                    select.appendChild(option);
                }
                
                // Reset selection
                selectedPromotion = null;
                displayPromotionDetails(null);
            }

            // Function to display promotion details
            function displayPromotionDetails(promo) {
                const detailsDiv = document.getElementById('promotion_details');
                if (!promo) {
                    detailsDiv.style.display = 'none';
                    return;
                }

                detailsDiv.style.display = 'block';

                // Promotion name and code
                document.getElementById('promotion_name').textContent = promo.name;
                document.getElementById('promotion_code_display').textContent = 'Mã: ' + promo.code;

                // Scope badge
                const scopeBadge = document.getElementById('promotion_scope_badge');
                if (promo.scope === 'order') {
                    scopeBadge.textContent = 'HÓA ĐƠN';
                    scopeBadge.className = 'badge bg-primary';
                } else {
                    scopeBadge.textContent = 'HẠNG KHÁCH HÀNG';
                    scopeBadge.className = 'badge bg-warning text-dark';
                }

                // Discount info
                let discountInfo = '';
                if (promo.discount_type === 'percent') {
                    discountInfo = 'Giảm ' + (promo.discount_percent || 0) + '%';
                    // Check if max_discount_amount exists and is greater than 0
                    // Handle both string and number types
                    const maxDiscount = promo.max_discount_amount !== undefined && promo.max_discount_amount !== null 
                        ? parseFloat(promo.max_discount_amount) 
                        : null;
                    if (maxDiscount !== null && !isNaN(maxDiscount) && maxDiscount > 0) {
                        discountInfo += ' (tối đa ' + new Intl.NumberFormat('vi-VN').format(maxDiscount) + 'đ)';
                    }
                } else {
                    discountInfo = 'Giảm ' + new Intl.NumberFormat('vi-VN').format(promo.discount_amount || 0) + 'đ';
                }
                document.getElementById('promotion_discount_info').textContent = discountInfo;

                // Conditions
                let conditions = [];
                if (promo.min_order_amount) {
                    conditions.push('Đơn hàng tối thiểu: ' + new Intl.NumberFormat('vi-VN').format(promo.min_order_amount) + 'đ');
                }
                if (promo.min_customer_tier) {
                    conditions.push('Từ hạng ' + promo.min_customer_tier + ' trở lên');
                }
                if (promo.per_user_limit) {
                    const usageText = promo.remaining_usage !== null 
                        ? 'Mỗi khách hàng: ' + promo.remaining_usage + '/' + promo.per_user_limit + ' lần'
                        : 'Mỗi khách hàng: ' + promo.per_user_limit + ' lần';
                    conditions.push(usageText);
                }
                if (promo.usage_limit) {
                    conditions.push('Số lượt sử dụng: ' + promo.usage_limit + ' lượt');
                }
                if (promo.start_date) {
                    conditions.push('Bắt đầu: ' + promo.start_date);
                }
                if (promo.end_date) {
                    conditions.push('Kết thúc: ' + promo.end_date);
                }

                const conditionsDiv = document.getElementById('promotion_conditions');
                if (conditions.length > 0) {
                    conditionsDiv.innerHTML = '<div class="mt-2"><strong>Điều kiện áp dụng:</strong></div>' +
                        '<ul class="mb-0 mt-1" style="padding-left: 20px;">' +
                        conditions.map(c => '<li>' + c + '</li>').join('') +
                        '</ul>';
                } else {
                    conditionsDiv.innerHTML = '';
                }
            }

            // Handle promotion scope radio change
            document.querySelectorAll('.promotion-scope-radio').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        const scope = this.value;
                        const select = document.getElementById('promotion_select');
                        const label = document.getElementById('promotion_select_label');
                        const appliedPromotionId = document.getElementById('applied_promotion_id');
                        
                        // Check if there's an applied promotion, remove it when switching tabs
                        if (appliedPromotionId && appliedPromotionId.value) {
                            const appointmentId = {{ $appointment->id }};
                            
                            // Remove applied promotion via AJAX, then reload with scope parameter
                            $.ajax({
                                url: '{{ route("admin.appointments.remove-coupon") }}',
                                method: 'GET',
                                data: {
                                    appointment_id: appointmentId
                                },
                                success: function(response) {
                                    // After removing, reload page with scope parameter to maintain selected tab
                                    const currentUrl = new URL(window.location.href);
                                    currentUrl.searchParams.set('promotion_scope', scope);
                                    window.location.href = currentUrl.toString();
                                },
                                error: function(xhr) {
                                    console.error('Error removing promotion:', xhr);
                                    // Continue with tab switching even if removal fails
                                    updateTabUI(scope, select, label);
                                }
                            });
                        } else {
                            // No applied promotion, just switch tab
                            updateTabUI(scope, select, label);
                        }
                    }
                });
            });

            // Function to update tab UI
            function updateTabUI(scope, select, label) {
                // Update label styles
                document.querySelectorAll('.promotion-scope-radio').forEach(function(r) {
                    const labelEl = document.querySelector('label[for="' + r.id + '"]');
                    if (labelEl) {
                        if (r.checked) {
                            labelEl.style.background = '#e7f3ff';
                            labelEl.style.borderColor = '#0d6efd';
                        } else {
                            labelEl.style.background = '#f9f9f9';
                            labelEl.style.borderColor = '#ddd';
                        }
                    }
                });

                // Update label text
                if (label) {
                    if (scope === 'order') {
                        label.textContent = 'Chọn mã khuyến mãi áp dụng theo hóa đơn:';
                    } else {
                        label.textContent = 'Chọn mã khuyến mãi áp dụng theo hạng khách hàng:';
                    }
                }
                
                // Populate select with appropriate promotions
                if (scope === 'order') {
                    populatePromotionSelect(orderPromotions);
                } else if (scope === 'customer_tier') {
                    populatePromotionSelect(customerTierPromotions);
                }

                // Reset selection
                if (select) {
                    select.value = '';
                }
                selectedPromotion = null;
                displayPromotionDetails(null);
            }

            // Get promotion select element (make it accessible globally in this scope)
            const promotionSelect = document.getElementById('promotion_select');
            
            // Handle promotion select change
            if (promotionSelect) {
                promotionSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        try {
                            const promoDataStr = selectedOption.dataset.promotion;
                            if (promoDataStr && promoDataStr !== 'undefined') {
                                const promoData = JSON.parse(promoDataStr);
                                selectedPromotion = promoData;
                                displayPromotionDetails(promoData);
                            } else {
                                selectedPromotion = null;
                                displayPromotionDetails(null);
                            }
                        } catch (e) {
                            console.error('Error parsing promotion data:', e, selectedOption);
                            selectedPromotion = null;
                            displayPromotionDetails(null);
                            // Don't show alert on every change, only log error
                        }
                    } else {
                        selectedPromotion = null;
                        displayPromotionDetails(null);
                    }
                });
            }

            // Function to apply promotion
            function applyPromotion(promotionCode, promotionId = null) {
                if (isApplyingPromotion) {
                    console.log('Already applying promotion, skipping...');
                    return;
                }
                
                if (!promotionCode || promotionCode.trim() === '') {
                    alert('Mã khuyến mãi không hợp lệ!');
                    return;
                }
                
                // Check if jQuery is available
                if (typeof $ === 'undefined') {
                    alert('Lỗi: jQuery chưa được tải. Vui lòng tải lại trang.');
                    console.error('jQuery is not loaded');
                    return;
                }
                
                isApplyingPromotion = true;
                const appointmentId = {{ $appointment->id }};
                const messageDiv = document.getElementById('promotion_message');
                const btnApply = document.getElementById('btn_apply_promotion');
                const selectEl = document.getElementById('promotion_select');
                
                // Disable button and select while applying
                if (btnApply) {
                    btnApply.disabled = true;
                    const originalHtml = btnApply.innerHTML;
                    btnApply.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang áp dụng...';
                    btnApply.dataset.originalHtml = originalHtml;
                }
                if (selectEl) {
                    selectEl.disabled = true;
                }
                
                // Show loading
                if (messageDiv) {
                    messageDiv.innerHTML = '<span class="text-info"><i class="fa fa-spinner fa-spin"></i> Đang áp dụng mã khuyến mãi...</span>';
                    messageDiv.style.display = 'block';
                }

                // Call apply-coupon route via AJAX
                $.ajax({
                    url: '{{ route("admin.appointments.apply-coupon") }}',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        _token: '{{ csrf_token() }}',
                        appointment_id: appointmentId,
                        coupon_code: promotionCode,
                        applied_promotion_id: promotionId
                    },
                    success: function(response) {
                        // Check if response indicates success
                        if (response && response.success) {
                            // Get discount amount from response
                            let discountAmount = 0;
                            if (response.promotion) {
                                // Try to get discount_amount from response
                                if (response.promotion.discount_amount !== undefined && response.promotion.discount_amount !== null) {
                                    discountAmount = parseFloat(response.promotion.discount_amount);
                                    // If parseFloat returns NaN, set to 0
                                    if (isNaN(discountAmount)) {
                                        discountAmount = 0;
                                    }
                                }
                            }
                            
                            // Update prices on the page without reloading
                            updatePrices(discountAmount);
                            
                            // Update applied promotion ID in form
                            if (response.promotion && response.promotion.id) {
                                const formPromotionId = document.getElementById('form_applied_promotion_id');
                                if (formPromotionId) {
                                    formPromotionId.value = response.promotion.id;
                                }
                            }
                            
                            // Update promotion details display if promotion info is available
                            if (response.promotion) {
                                // Parse max_discount_amount to number if it exists (handle both string and number)
                                let maxDiscountAmount = null;
                                if (response.promotion.max_discount_amount !== undefined && response.promotion.max_discount_amount !== null) {
                                    const parsed = parseFloat(response.promotion.max_discount_amount);
                                    maxDiscountAmount = !isNaN(parsed) ? parsed : null;
                                } else if (selectedPromotion && selectedPromotion.max_discount_amount !== undefined) {
                                    const parsed = parseFloat(selectedPromotion.max_discount_amount);
                                    maxDiscountAmount = !isNaN(parsed) ? parsed : null;
                                }
                                
                                const promoData = {
                                    id: response.promotion.id,
                                    code: response.promotion.code,
                                    name: response.promotion.name || response.promotion.code,
                                    scope: response.promotion.apply_scope || (selectedPromotion ? selectedPromotion.scope : 'order'),
                                    discount_type: response.promotion.discount_type || (selectedPromotion ? selectedPromotion.discount_type : 'amount'),
                                    discount_percent: response.promotion.discount_percent !== undefined ? response.promotion.discount_percent : (selectedPromotion ? selectedPromotion.discount_percent : 0),
                                    discount_amount: response.promotion.discount_amount !== undefined ? parseFloat(response.promotion.discount_amount) || 0 : 0,
                                    max_discount_amount: maxDiscountAmount
                                };
                                // Update selectedPromotion cache
                                selectedPromotion = promoData;
                                displayPromotionDetails(promoData);
                                
                                // Show promotion details and remove button after successful application
                                const promotionDetails = document.getElementById('promotion_details');
                                if (promotionDetails) {
                                    promotionDetails.style.display = 'block';
                                    
                                    // Show remove button if not already visible
                                    const btnRemove = document.getElementById('btn_remove_promotion');
                                    if (!btnRemove) {
                                        // Create remove button if it doesn't exist
                                        const removeBtnContainer = document.createElement('div');
                                        removeBtnContainer.className = 'mt-3';
                                        removeBtnContainer.innerHTML = '<button type="button" class="btn btn-sm btn-outline-danger" id="btn_remove_promotion" title="Xóa mã"><i class="fa fa-times"></i> Xóa mã đã áp dụng</button>';
                                        promotionDetails.appendChild(removeBtnContainer);
                                        
                                        // Attach event listener to the new button
                                        const newBtnRemove = document.getElementById('btn_remove_promotion');
                                        if (newBtnRemove) {
                                            attachRemoveButtonListener(newBtnRemove);
                                        }
                                    } else {
                                        btnRemove.style.display = 'block';
                                    }
                                }
                            }
                            
                            // Show success message
                            if (messageDiv) {
                                messageDiv.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> ' + (response.message || 'Áp dụng mã khuyến mãi thành công!') + '</span>';
                                messageDiv.style.display = 'block';
                            }
                            
                            // Re-enable button and select
                            isApplyingPromotion = false;
                            if (btnApply) {
                                btnApply.disabled = false;
                                const originalHtml = btnApply.dataset.originalHtml || '<span class="d-none d-sm-inline">Áp dụng</span>';
                                btnApply.innerHTML = originalHtml;
                            }
                            if (selectEl) {
                                selectEl.disabled = false;
                            }
                        } else {
                            // Response indicates failure
                            isApplyingPromotion = false;
                            
                            // Re-enable button and select
                            if (btnApply) {
                                btnApply.disabled = false;
                                const originalHtml = btnApply.dataset.originalHtml || '<span class="d-none d-sm-inline">Áp dụng</span>';
                                btnApply.innerHTML = originalHtml;
                            }
                            if (selectEl) {
                                selectEl.disabled = false;
                            }
                            
                            const errorMessage = (response && response.message) ? response.message : 'Có lỗi xảy ra khi áp dụng mã khuyến mãi';
                            if (messageDiv) {
                                messageDiv.innerHTML = '<span class="text-danger"><i class="fa fa-exclamation-circle"></i> ' + errorMessage + '</span>';
                                messageDiv.style.display = 'block';
                            }
                        }
                    },
                    error: function(xhr) {
                        isApplyingPromotion = false;
                        
                        // Re-enable button and select
                        if (btnApply) {
                            btnApply.disabled = false;
                            const originalHtml = btnApply.dataset.originalHtml || '<span class="d-none d-sm-inline">Áp dụng</span>';
                            btnApply.innerHTML = originalHtml;
                        }
                        if (selectEl) {
                            selectEl.disabled = false;
                        }
                        
                        let errorMessage = 'Có lỗi xảy ra khi áp dụng mã khuyến mãi';
                        
                        // Try to get error message from response
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            } else if (xhr.responseJSON.errors) {
                                // Validation errors
                                const errors = xhr.responseJSON.errors;
                                const firstError = Object.values(errors)[0];
                                errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                            }
                        } else if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                } else if (response.error) {
                                    errorMessage = response.error;
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e, xhr.responseText);
                            }
                        }
                        
                        if (messageDiv) {
                            messageDiv.innerHTML = '<span class="text-danger"><i class="fa fa-exclamation-circle"></i> ' + errorMessage + '</span>';
                            messageDiv.style.display = 'block';
                        }
                        
                        console.error('AJAX Error:', xhr);
                    }
                });
            }

            // Handle apply button click
            const btnApplyPromotion = document.getElementById('btn_apply_promotion');
            if (btnApplyPromotion) {
                btnApplyPromotion.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Get selected promotion from dropdown
                    const select = document.getElementById('promotion_select');
                    if (!select || !select.value) {
                        alert('Vui lòng chọn mã khuyến mãi trước khi áp dụng!');
                        return;
                    }
                    
                    const selectedOption = select.options[select.selectedIndex];
                    if (!selectedOption || !selectedOption.value) {
                        alert('Vui lòng chọn mã khuyến mãi trước khi áp dụng!');
                        return;
                    }
                    
                    // Try to get promotion data
                    let promoCode = '';
                    let promoId = null;
                    
                    // First try to use cached promotion data
                    if (selectedPromotion && selectedPromotion.code) {
                        promoCode = selectedPromotion.code;
                        promoId = selectedPromotion.id;
                    } else {
                        // Try to parse from dataset
                        try {
                            const promoDataStr = selectedOption.dataset.promotion;
                            if (promoDataStr && promoDataStr !== 'undefined' && promoDataStr.trim() !== '') {
                                const promoData = JSON.parse(promoDataStr);
                                if (promoData && promoData.code) {
                                    promoCode = promoData.code;
                                    promoId = promoData.id || null;
                                    // Cache it for next time
                                    selectedPromotion = promoData;
                                }
                            }
                        } catch (e) {
                            console.error('Error parsing promotion data:', e, selectedOption);
                        }
                        
                        // If still no code, try to extract from text
                        if (!promoCode) {
                            const textParts = selectedOption.textContent.split(' - ');
                            if (textParts.length > 0) {
                                promoCode = textParts[0].trim();
                            }
                        }
                    }
                    
                    if (!promoCode || promoCode === '') {
                        alert('Không thể lấy thông tin mã khuyến mãi. Vui lòng chọn lại mã khuyến mãi.');
                        return;
                    }
                    
                    // Call applyPromotion function
                    try {
                        applyPromotion(promoCode, promoId);
                    } catch (error) {
                        console.error('Error calling applyPromotion:', error);
                        alert('Có lỗi xảy ra khi áp dụng mã khuyến mãi. Vui lòng thử lại.');
                        isApplyingPromotion = false;
                    }
                });
            }

            // Initialize: Load promotions based on selected scope
            @if($appliedCoupon && \Illuminate\Support\Facades\Session::has('coupon_code'))
                @php
                    $appliedPromoData = [
                        'id' => $appliedCoupon->id,
                        'code' => $appliedCoupon->code,
                        'name' => $appliedCoupon->name,
                        'scope' => $appliedCoupon->apply_scope,
                        'discount_type' => $appliedCoupon->discount_type,
                        'discount_percent' => $appliedCoupon->discount_percent ?? 0,
                        'discount_amount' => $appliedCoupon->discount_amount ?? 0,
                        'max_discount_amount' => $appliedCoupon->max_discount_amount ?? 0,
                        'min_order_amount' => $appliedCoupon->min_order_amount ?? 0,
                        'min_customer_tier' => $appliedCoupon->min_customer_tier ?? null,
                        'per_user_limit' => $appliedCoupon->per_user_limit ?? 0,
                    ];
                @endphp
                const appliedPromo = @json($appliedPromoData);
                
                // Display promotion details since promotion is already applied
                displayPromotionDetails(appliedPromo);
                
                // Show promotion details and remove button
                const promotionDetails = document.getElementById('promotion_details');
                if (promotionDetails) {
                    promotionDetails.style.display = 'block';
                }
                
                const btnRemove = document.getElementById('btn_remove_promotion');
                if (btnRemove) {
                    btnRemove.style.display = 'block';
                }
                
                // Set scope radio (but don't trigger change to avoid reload)
                const scopeRadio = document.querySelector('.promotion-scope-radio[value="' + appliedPromo.scope + '"]');
                if (scopeRadio) {
                    scopeRadio.checked = true;
                    // Update tab UI without triggering change event
                    const select = document.getElementById('promotion_select');
                    const label = document.getElementById('promotion_select_label');
                    updateTabUI(appliedPromo.scope, select, label);
                }
            @else
                // If no promotion applied, check URL parameter or default to 'order' scope
                @php
                    $selectedScope = request('promotion_scope', 'order');
                @endphp
                const selectedScope = @json($selectedScope);
                const scopeRadio = document.querySelector('.promotion-scope-radio[value="' + selectedScope + '"]');
                if (scopeRadio) {
                    scopeRadio.checked = true;
                    scopeRadio.dispatchEvent(new Event('change'));
                }
            @endif

            // Function to attach remove button listener
            function attachRemoveButtonListener(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const appointmentId = {{ $appointment->id }};
                    const messageDiv = document.getElementById('promotion_message');
                    
                    // Show loading
                    if (messageDiv) {
                        messageDiv.innerHTML = '<span class="text-info"><i class="fa fa-spinner fa-spin"></i> Đang xóa mã khuyến mãi...</span>';
                        messageDiv.style.display = 'block';
                    }
                    
                    // Disable button
                    this.disabled = true;
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xóa...';
                    
                    // Call remove-coupon route via AJAX
                    $.ajax({
                        url: '{{ route("admin.appointments.remove-coupon") }}',
                        method: 'GET',
                        data: {
                            appointment_id: appointmentId
                        },
                        success: function(response) {
                            // Update prices (remove discount)
                            updatePrices(0);
                            
                            // Hide promotion details
                            const promotionDetails = document.getElementById('promotion_details');
                            if (promotionDetails) {
                                promotionDetails.style.display = 'none';
                            }
                            
                            // Hide remove button
                            const btnRemove = document.getElementById('btn_remove_promotion');
                            if (btnRemove) {
                                btnRemove.style.display = 'none';
                            }
                            
                            // Clear selected promotion
                            selectedPromotion = null;
                            const select = document.getElementById('promotion_select');
                            if (select) {
                                select.value = '';
                            }
                            
                            // Reset form fields
                            const formPromotionId = document.getElementById('form_applied_promotion_id');
                            if (formPromotionId) {
                                formPromotionId.value = '';
                            }
                            
                            // Show success message
                            if (messageDiv) {
                                messageDiv.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> Đã xóa mã khuyến mãi thành công!</span>';
                                messageDiv.style.display = 'block';
                            }
                            
                            // Re-enable button
                            if (button) {
                                button.disabled = false;
                                button.innerHTML = originalText;
                            }
                            
                            // Reload page after a short delay to refresh the view
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        },
                        error: function(xhr) {
                            // Re-enable button
                            if (button) {
                                button.disabled = false;
                                button.innerHTML = originalText;
                            }
                            
                            let errorMessage = 'Có lỗi xảy ra khi xóa mã khuyến mãi';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            
                            if (messageDiv) {
                                messageDiv.innerHTML = '<span class="text-danger"><i class="fa fa-exclamation-circle"></i> ' + errorMessage + '</span>';
                                messageDiv.style.display = 'block';
                            }
                            
                            console.error('AJAX Error:', xhr);
                        }
                    });
                });
            }

            // Handle remove promotion button click (for existing button)
            const btnRemovePromotion = document.getElementById('btn_remove_promotion');
            if (btnRemovePromotion) {
                attachRemoveButtonListener(btnRemovePromotion);
            }

            // Initialize with already applied promotion
        });
    </script>
@endpush