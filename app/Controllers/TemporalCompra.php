<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TemporalCompraModel;
use App\Models\ProductosModel;

class TemporalCompra extends BaseController
{
    protected $temporal_compra, $productos;
    

    public function __construct()
    {
        $this->temporal_compra = new TemporalCompraModel();
        $this->productos = new ProductosModel();
        
    }

    public function inserta($id_producto, $cantidad, $id_compra)
    {
        $error ='';
        $producto = $this->productos->where('id',$id_producto)->first();

        if($producto){
            $datosExiste = $this->temporal_compra->porIdProductoCompra($id_producto, $id_compra);
            if($datosExiste){
                $cantidad = $datosExiste->cantidad + $cantidad;
                $subtotal = $cantidad * $datosExiste->precio;
            }else{
                $subtotal = $cantidad * $producto['precio_compra'];

                $this->temporal_compra->save([
                    'filio' => $id_compra,
                    'id_producto' => $id_producto,
                    'codigo' => $producto['codigo'],
                    'nombre' => $producto['nombre'],
                    'precio' => $producto['precio_compra'],
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal
                ]);
            }
        }else{
            $error = 'No existe el producto';
        }

        $res['datos'] = $this->cargaProductos($id_compra);
        $res['total'] = $this->totalProductos($id_compra);
        $res['error'] = $error;
        echo json_encode($res);

    }

    public function totalProductos ($id_compra){
        $resultado = $this->temporal_compra->porCompra($id_compra);
        $total = 0;
        foreach($resultado as $row){

            $total += $row['subtotal'];
        }
        return $total;
    }

    public function cargaProductos($id_compra){
        $resultado = $this->temporal_compra->porCompra($id_compra);
        $fila = '';
        $numFila = 0;

        foreach($resultado as $row){
            $numFila++;
            $fila .= "<tr id='fila".$numFila."'>";
            $fila .="<td>".$row['codigo']."</td>";
            $fila .="<td>".$row['nombre']."</td>";
            $fila .="<td>".$row['precio']."</td>";
            $fila .="<td>".$row['cantidad']."</td>";
            $fila .="<td>".$row['subtotal']."</td>";
            $fila .="<td><a onclick=\"eliminaProducto(".$row['id_producto'].",'".$id_compra."')\"
            class='borrar'><span class='fas fa-fw fa-trash'></span></a></td>";

            $fila .="</tr>";
        }
        return $fila;
    }

}