import requests
import faker
import random
def generate_transaction():
    pass

    fake = faker.Faker()

    # Generate Name
    name = fake.name()
    # Generate Enail Address
    email = fake.email()
    payment = random.choice(['assessment', 'registrar'])

    # make request
    host = 'http://localhost:8080'
    url = f'{host}/public/api/api_endpoint.php'
    headers = {
        'Content-Type': 'application/json'
    }
    data = {
        "method" : "requester_form",
        "name" : name,
        "email" : email,
        "payment" : payment,
        "website" : "example.com",
    }
    response = requests.post(url, headers=headers, json=data)
    # Check if the request was successful
    if response.status_code == 200:
        print(f"Transaction successful: {response.json()}")
    else:
        print(f"Transaction failed: {response.status_code} - {response.text}")

for i in range(10):
    generate_transaction()



