total_data = 74
pages = [1, 2, 3, 4, 5]

page = 1
paginate = 5
total_page = total_data // paginate

while True:
    print(f"Page Nav: {pages}")
    print(f'Page: {page}/{total_page}')
    set_page = int(input('Enter Page: '))

    if set_page > total_page:
        page = total_page
    elif set_page < 0:
        page = 1
    else:
        page = set_page

    if page > 3:
        pages = [page - 2, page - 1, page, page + 1, page + 2]
    elif page <= 3:
        page = 3
        pages = [1, 2, page, page + 1, page + 2]

    # Ensure pages don't exceed total_page
    pages = [p for p in pages if p <= total_page]

    # Ensure pages don't always 1,1,1 in array pages
    