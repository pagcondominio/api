<?php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://payment.safe2pay.com.br/v2/Payment',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "IsSandbox": true,
    "Application": "Testando Subconta",
    "Vendor": "Pagcondominio.com",
    "CallbackUrl": "https://callbacks.exemplo.com.br/api/Notify",
    "PaymentMethod": "1",
    "Reference": "TESTE NUMBER 4!",
    "Customer": {
        "Name": "João Jorge Jesus.",
        "Identity": "80363002065",
        "Phone": "51999999999",
        "Email": "joaojfl@pagcondominio.com",
        "Address": {
            "ZipCode": "90670090",
            "Street": "Logradouro",
            "Number": "123",
            "Complement": "Complemento",
            "District": "Higienopolis",
            "CityName": "Porto Alegre",
            "StateInitials": "RS",
            "CountryName": "Brasil"
        }
    },
    "Products": [
        {
            "Code": "001",
            "Description": "Condominio",
            "UnitPrice": '.$valor_cobrado.',
            "Quantity": 1
        }
    ],
    "PaymentObject": {
        "DueDate": "10/10/2021",
        "Instruction": "Instruções",
        "Message": [
            "Mensagem um",
            "Mensagem dois"
        ],
        "PenaltyRate": 2,
        "InterestRate": 0.4,
        "CancelAfterDue": false,
        "IsEnablePartialPayment": false,
        "DiscountType": "1",
        "DiscountAmount": 5,
        "DiscountDue": "30/09/2021"
    }
}',
  CURLOPT_HTTPHEADER => array(
    'x-api-key: AEE154C13A4A41FBA99A23CC896FD3B7',
    'Content-Type: application/json',
    'Cookie: .AspNetCore.Session=CfDJ8Gb%2BFr1pKTBFoMuwXxZ5oUUZVxvNiuLMa%2BYWL12kghWnZ09EN%2BkuJj6luNTQwl378heVuPEW9KGhPHM2cuhranJEB%2FJyMeM0yUeotLlHvEbzP40CU5qIfBNjESLbX588YiLQ20V8M1G3zW3AL868H5nnEuBNSfghJDJXlAzufz7w; ARRAffinity=89e0c856cf114838a8f61309674a21cc1774a7106e5f4f7e1780e27e7b7a01a4; ARRAffinitySameSite=89e0c856cf114838a8f61309674a21cc1774a7106e5f4f7e1780e27e7b7a01a4; TiPMix=77.2254074817642; x-ms-routing-name=self'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
//echo $response;
//echo '<hr>';
$bodycurl = json_decode($response, true);
/*
echo "DigitableLine: ".$bodycurl["ResponseDetail"]["DigitableLine"];
echo '<br>';
echo "Barcode: ".$bodycurl["ResponseDetail"]["Barcode"];
echo '<br>';
echo $bodycurl["ResponseDetail"]["IdTransaction"];
echo '<hr>';
*/