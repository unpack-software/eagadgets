@extends('layouts.admin')

@section('title', 'Editar Produto')

@section('main')


<style>
    @media only screen and (min-width: 1000px) {
        .min-width-main-card {
            min-width: 50%;
        }
    }

    @media only screen and (max-width: 999px) {
        .min-width-main-card {
            min-width: 70%;
        }
    }

    @media only screen and (max-width: 599px) {
        .min-width-main-card {
            max-width: 80%;
        }
    }

    @media only screen and (max-width: 199px) {
        .min-width-main-card {
            min-width: 100%;
        }
    }
</style>

<div class="card min-width-main-card">
    <div class="card-body">

        @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
            {{ $error }}
            @endforeach
        </div>
        @endif

        <div class="">
            <h3>Editar Produto</h3>
            <form action="{{route('product.update', $product->id)}}" id="formProductCreation" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Nome do produto</label>
                    <input type="text" maxlength="255" value="{{$product->name}}" required class="form-control" name="name" id="name">
                </div>
                <div class="form-group">
                    <label for="name">Preço do produto</label>
                    <input type="number" step=".01" min="0" value="{{$product->price}}" required class="form-control" name="price" id="price">
                </div>


                <div class="form-group">
                    <label for="category_id">Categoria do producto</label>
                    <select class="form-control" name="category_id" id="category_id">
                        @foreach($categories as $category)
                            <option @if($category->id == $product->category->id) {{'selected'}} @endif value="{{$category->id}}">{{$category->name}}</option>
                        @endforeach
                    </select>
                </div>


                <div class="inpu-group">

                    <div class="box">
                        <input type="file" name="image" id="file-1" class="inputfile inputfile-1" data-multiple-caption="{count} files selected" multiple />
                        <label for="file-1"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17">
                                <path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z" /></svg> <span>Escolha uma foto&hellip;</span></label>
                    </div>
                
                </div>

                <div>
                    <b>Descrição (copie o texto abaixo para o editor, se quiser editar. Se deixar o editor vazio, a descrição não vai ser alterada)</b>
                    <div>
                        {!!$product->description!!}
                    </div>
                </div>

                <div class="form-group" style="margin-top: 30px;">
                    <h6 for="name">Descrição do produto</h6>
                    <div id="editor"></div>
                    <span class="red-text" id="descriptionErrorMessage">É necessário escrever uma descrição mais longa</span>
                </div>
                <div>
                    
                </div>
                <input type="hidden" name="description" id="description">
                <button type="submit" class="btn btn-primary">Editar Produto</button>
            </form>
        </div>
    </div>
</div>

<style>
    .red-text {
        color: #d3394c;
    }

    button,
    input {
        display: none;
        overflow: visible;
    }

    .js .inputfile {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }

    .inputfile+label {
        max-width: 80%;
        font-size: 1.25rem;
        /* 20px */
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
        display: inline-block;
        overflow: hidden;
        padding: 0.625rem 1.25rem;
        /* 10px 20px */
    }

    .no-js .inputfile+label {
        display: none;
    }

    .inputfile:focus+label,
    .inputfile.has-focus+label {
        outline: 1px dotted #000;
        outline: -webkit-focus-ring-color auto 5px;
    }


    .inputfile+label svg {
        width: 1em;
        height: 1em;
        vertical-align: middle;
        fill: currentColor;
        margin-top: -0.25em;
        /* 4px */
        margin-right: 0.25em;
        /* 4px */
    }


    /* style 1 */

    .inputfile-1+label {
        color: #f1e5e6;
        background-color: #d3394c;
    }

    .inputfile-1:focus+label,
    .inputfile-1.has-focus+label,
    .inputfile-1+label:hover {
        background-color: #722040;
    }
</style>

<script>
    var quill = new Quill('#editor', {
        theme: 'snow'
    })
    $(document).ready(function() {

        $('#descriptionErrorMessage').hide()
        $('#description').val('{{$product->description}}');
       
        
        
        $('#formProductCreation').submit((e) => {
            var html = quill.root.innerHTML;

            // console.log($('#file-1').files.length);
            
            if (false) {
                $('#descriptionErrorMessage').show()
              e.preventDefault()

            } else {
                $('#descriptionErrorMessage').hide()
            }
            $('#description').val(html)
        })
    });


    ;
    (function($, window, document, undefined) {
        $('.inputfile').each(function() {
            var $input = $(this),
                $label = $input.next('label'),
                labelVal = $label.html();

            $input.on('change', function(e) {
                var fileName = '';

                if (this.files && this.files.length > 1)
                    fileName = (this.getAttribute('data-multiple-caption') || '').replace('{count}', this.files.length);
                else if (e.target.value)
                    fileName = e.target.value.split('\\').pop();

                if (fileName)
                    $label.find('span').html(fileName);
                else
                    $label.html(labelVal);
            });

            // Firefox bug fix
            $input
                .on('focus', function() {
                    $input.addClass('has-focus');
                })
                .on('blur', function() {
                    $input.removeClass('has-focus');
                });
        });
    })(jQuery, window, document);
</script>
@endsection