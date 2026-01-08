DROP TABLE IF EXISTS galeria_producto CASCADE;
DROP TABLE IF EXISTS galeria_sucursal CASCADE;
DROP TABLE IF EXISTS item_coleccion CASCADE;
DROP TABLE IF EXISTS coleccion CASCADE;
DROP TABLE IF EXISTS historial_vista CASCADE;
DROP TABLE IF EXISTS item_guardado CASCADE;
DROP TABLE IF EXISTS lista_deseos CASCADE;
DROP TABLE IF EXISTS log_interaccion_ia CASCADE;
DROP TABLE IF EXISTS uso_promocion CASCADE;
DROP TABLE IF EXISTS resena_sucursal CASCADE;
DROP TABLE IF EXISTS resena_producto CASCADE;
DROP TABLE IF EXISTS historial_pedido CASCADE;
DROP TABLE IF EXISTS transaccion_pago CASCADE;
DROP TABLE IF EXISTS billetera_usuario CASCADE;
DROP TABLE IF EXISTS detalle_pedido CASCADE;
DROP TABLE IF EXISTS pedido CASCADE;
DROP TABLE IF EXISTS orden_compra CASCADE;
DROP TABLE IF EXISTS item_carrito CASCADE;
DROP TABLE IF EXISTS carrito CASCADE;
DROP TABLE IF EXISTS promocion CASCADE;
DROP TABLE IF EXISTS inventario CASCADE;
DROP TABLE IF EXISTS producto CASCADE;
DROP TABLE IF EXISTS categoria CASCADE;
DROP TABLE IF EXISTS colaborador CASCADE;
DROP TABLE IF EXISTS config_entrega CASCADE;
DROP TABLE IF EXISTS horario CASCADE;
DROP TABLE IF EXISTS sucursal CASCADE;
DROP TABLE IF EXISTS negocio CASCADE;
DROP TABLE IF EXISTS direccion_usuario CASCADE;
DROP TABLE IF EXISTS usuario_rol CASCADE;
DROP TABLE IF EXISTS rol CASCADE;
DROP TABLE IF EXISTS usuario CASCADE;
DROP TYPE IF EXISTS tipo_descuento CASCADE;
DROP TYPE IF EXISTS alcance_promo CASCADE;
DROP TYPE IF EXISTS tipo_metodo_pago CASCADE;
DROP TYPE IF EXISTS estado_transaccion CASCADE;
DROP TYPE IF EXISTS proveedor_pago CASCADE;
DROP TYPE IF EXISTS tipo_entrega CASCADE;
DROP TYPE IF EXISTS estado_pedido CASCADE;
DROP TYPE IF EXISTS canal_venta CASCADE;
DROP TYPE IF EXISTS rol_sucursal CASCADE;

CREATE EXTENSION IF NOT EXISTS "pgcrypto";

CREATE TYPE estado_pedido AS ENUM ('pendiente', 'pagado', 'preparando', 'en_camino', 'entregado', 'cancelado');
CREATE TYPE canal_venta AS ENUM ('app', 'ia_voz', 'ia_texto', 'web');
CREATE TYPE rol_sucursal AS ENUM ('empleado');
CREATE TYPE tipo_entrega AS ENUM ('delivery', 'recojo', 'mesa');
CREATE TYPE tipo_descuento AS ENUM ('porcentaje', 'monto_fijo', 'envio_gratis');
CREATE TYPE alcance_promo AS ENUM ('producto', 'sucursal', 'global');
CREATE TYPE tipo_metodo_pago AS ENUM ('efectivo', 'tarjeta_credito', 'tarjeta_debito', 'qr_bancario', 'billetera_movil');
CREATE TYPE estado_transaccion AS ENUM ('pendiente', 'aprobado', 'rechazado', 'reembolsado', 'expirado');
CREATE TYPE proveedor_pago AS ENUM ('stripe', 'cybersource', 'libelula', 'pasarela_qr_local', 'efectivo_manual', 'simulado', 'transferencia_manual');

CREATE TABLE rol (
    id_rol              uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    nombre              text NOT NULL UNIQUE,
    descripcion         text,
    activo              boolean DEFAULT true
);

CREATE TABLE usuario (
    id_usuario          uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    nombres             text NOT NULL,
    apellidos           text NOT NULL,
    correo              text UNIQUE,
    password_hash       text NOT NULL,
    telefono            text NOT NULL,
    ubicacion_actual    point,
    fecha_registro      timestamptz DEFAULT now(),
    activo              boolean DEFAULT true,
    CONSTRAINT uq_usuario_telefono UNIQUE (telefono)
);

CREATE TABLE usuario_rol (
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    id_rol              uuid NOT NULL REFERENCES rol(id_rol) ON DELETE RESTRICT,
    fecha_asignacion    timestamptz DEFAULT now(),
    PRIMARY KEY (id_usuario, id_rol)
);

CREATE TABLE direccion_usuario (
    id_direccion        uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    etiqueta            text NOT NULL,
    ubicacion_gps       point NOT NULL,
    direccion_texto     text NOT NULL,
    referencia          text,
    es_predeterminada   boolean DEFAULT false,
    ultima_fecha_uso    timestamptz DEFAULT now()
);

CREATE TABLE negocio (
    id_negocio          uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_propietario      uuid NOT NULL REFERENCES usuario(id_usuario),
    nombre              text NOT NULL,
    descripcion         text,
    logotipo_url        text,
    fecha_registro      timestamptz DEFAULT now(),
    activo              boolean DEFAULT true
);

CREATE TABLE sucursal (
    id_sucursal         uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_negocio          uuid NOT NULL REFERENCES negocio(id_negocio) ON DELETE CASCADE,
    nombre_sucursal     text NOT NULL,
    telefonos           text[],
    ubicacion_gps       point NOT NULL,
    zona_reparto        polygon,
    direccion_texto     text NOT NULL,
    imagen_qr_estatico_url text,
    imagen_portada_url  text,
    activo              boolean DEFAULT true
);

CREATE TABLE galeria_sucursal (
    id_foto             uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_sucursal         uuid NOT NULL REFERENCES sucursal(id_sucursal) ON DELETE CASCADE,
    url                 text NOT NULL,
    descripcion         text,
    orden_visual        int DEFAULT 0,
    fecha_subida        timestamptz DEFAULT now()
);

CREATE TABLE config_entrega (
    id_config           uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_sucursal         uuid NOT NULL REFERENCES sucursal(id_sucursal) ON DELETE CASCADE,
    tipo                tipo_entrega NOT NULL,
    costo_base          numeric(12, 2) NOT NULL DEFAULT 0 CHECK (costo_base >= 0),
    tiempo_min_aprox    int,
    activo              boolean DEFAULT true,
    UNIQUE (id_sucursal, tipo)
);

CREATE TABLE horario (
    id_horario          uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_sucursal         uuid NOT NULL REFERENCES sucursal(id_sucursal) ON DELETE CASCADE,
    dia_semana          int NOT NULL CHECK (dia_semana BETWEEN 0 AND 6),
    hora_apertura       time NOT NULL,
    hora_cierre         time NOT NULL,
    es_feriado          boolean DEFAULT false,
    CONSTRAINT chk_horas CHECK (hora_cierre > hora_apertura),
    UNIQUE (id_sucursal, dia_semana)
);

CREATE TABLE colaborador (
    id_sucursal         uuid NOT NULL REFERENCES sucursal(id_sucursal),
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario),
    rol                 rol_sucursal NOT NULL DEFAULT 'empleado',
    fecha_vinculo       timestamptz DEFAULT now(),
    activo              boolean DEFAULT true,
    PRIMARY KEY (id_sucursal, id_usuario)
);

CREATE TABLE categoria (
    id_categoria        int GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
    id_negocio          uuid NOT NULL REFERENCES negocio(id_negocio),
    nombre              text NOT NULL,
    UNIQUE (id_negocio, nombre)
);

CREATE TABLE producto (
    id_producto         uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_negocio          uuid NOT NULL REFERENCES negocio(id_negocio),
    id_categoria        int REFERENCES categoria(id_categoria),
    nombre              text NOT NULL,
    descripcion         text,
    codigo_sku          text,
    imagen_url          text,
    precio_base         numeric(12, 2) NOT NULL CHECK (precio_base >= 0),
    vector_busqueda     tsvector GENERATED ALWAYS AS (
                            setweight(to_tsvector('spanish', nombre), 'A') ||
                            setweight(to_tsvector('spanish', coalesce(descripcion, '')), 'B')
                        ) STORED,
    activo              boolean DEFAULT true
);

CREATE TABLE galeria_producto (
    id_foto             uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_producto         uuid NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    url                 text NOT NULL,
    orden_visual        int DEFAULT 0,
    fecha_subida        timestamptz DEFAULT now()
);

CREATE TABLE inventario (
    id_inventario       uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_sucursal         uuid NOT NULL REFERENCES sucursal(id_sucursal) ON DELETE CASCADE,
    id_producto         uuid NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    cantidad            int NOT NULL DEFAULT 0 CHECK (cantidad >= 0),
    precio_local        numeric(12, 2) CHECK (precio_local >= 0),
    activo              boolean DEFAULT true,
    ultima_actualizacion timestamptz DEFAULT now(),
    UNIQUE (id_sucursal, id_producto)
);

CREATE TABLE promocion (
    id_promocion        uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    nombre              text NOT NULL,
    codigo_cupon        text UNIQUE,
    descripcion         text,
    tipo_beneficio      tipo_descuento NOT NULL,
    valor_descuento     numeric(12, 2) NOT NULL DEFAULT 0 CHECK (valor_descuento >= 0),
    alcance             alcance_promo NOT NULL,
    id_sucursal         uuid REFERENCES sucursal(id_sucursal) ON DELETE CASCADE,
    id_producto         uuid REFERENCES producto(id_producto) ON DELETE CASCADE,
    fecha_inicio        timestamptz NOT NULL,
    fecha_fin           timestamptz NOT NULL,
    monto_minimo_compra numeric(12, 2) DEFAULT 0 CHECK (monto_minimo_compra >= 0),
    activo              boolean DEFAULT true,
    CONSTRAINT chk_alcance_valido CHECK (
        (alcance = 'global' AND id_sucursal IS NULL AND id_producto IS NULL) OR
        (alcance = 'sucursal' AND id_sucursal IS NOT NULL AND id_producto IS NULL) OR
        (alcance = 'producto' AND id_producto IS NOT NULL)
    ),
    CONSTRAINT chk_fechas_promo CHECK (fecha_fin > fecha_inicio)
);

CREATE TABLE carrito (
    id_carrito          uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario),
    ultima_modificacion timestamptz DEFAULT now(),
    CONSTRAINT uq_carrito_usuario UNIQUE (id_usuario)
);

CREATE TABLE item_carrito (
    id_item             bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_carrito          uuid NOT NULL REFERENCES carrito(id_carrito) ON DELETE CASCADE,
    id_producto         uuid NOT NULL REFERENCES producto(id_producto),
    id_sucursal_origen  uuid NOT NULL REFERENCES sucursal(id_sucursal),
    cantidad            int NOT NULL CHECK (cantidad > 0),
    observacion         text,
    fecha_agregado      timestamptz DEFAULT now()
);

CREATE TABLE item_guardado (
    id_guardado         bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    id_producto         uuid NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    id_sucursal_origen  uuid NOT NULL REFERENCES sucursal(id_sucursal),
    cantidad            int NOT NULL CHECK (cantidad > 0),
    observacion         text,
    fecha_guardado      timestamptz DEFAULT now()
);

CREATE TABLE orden_compra (
    id_orden_compra     uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario),
    importe_productos_total numeric(12, 2) NOT NULL DEFAULT 0,
    importe_delivery_total  numeric(12, 2) NOT NULL DEFAULT 0,
    importe_descuento_total numeric(12, 2) NOT NULL DEFAULT 0,
    importe_final_total     numeric(12, 2) NOT NULL DEFAULT 0,
    fecha_registro      timestamptz DEFAULT now()
);

CREATE TABLE pedido (
    id_pedido           uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_orden_compra     uuid NOT NULL REFERENCES orden_compra(id_orden_compra) ON DELETE CASCADE,
    numero_orden_publico bigint GENERATED ALWAYS AS IDENTITY,
    id_sucursal         uuid NOT NULL REFERENCES sucursal(id_sucursal),
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario),
    importe_subtotal    numeric(12, 2) NOT NULL DEFAULT 0,
    importe_envio       numeric(12, 2) NOT NULL DEFAULT 0,
    importe_descuento   numeric(12, 2) NOT NULL DEFAULT 0,
    importe_total       numeric(12, 2) NOT NULL DEFAULT 0,
    estado              estado_pedido DEFAULT 'pendiente',
    canal               canal_venta NOT NULL,
    tipo_entrega        tipo_entrega NOT NULL,
    ubicacion_entrega   point,
    direccion_texto     text,
    fecha_programada    timestamptz,
    fecha_creacion      timestamptz DEFAULT now()
);

CREATE TABLE detalle_pedido (
    id_detalle          bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_pedido           uuid NOT NULL REFERENCES pedido(id_pedido),
    id_producto         uuid NOT NULL REFERENCES producto(id_producto),
    cantidad            int NOT NULL CHECK (cantidad > 0),
    precio_unitario     numeric(12, 2) NOT NULL CHECK (precio_unitario >= 0),
    precio_original     numeric(12, 2),
    descuento_aplicado  numeric(12, 2) DEFAULT 0,
    subtotal            numeric(12, 2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED
);

CREATE TABLE uso_promocion (
    id_uso              uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_promocion        uuid NOT NULL REFERENCES promocion(id_promocion),
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario),
    id_orden_compra     uuid REFERENCES orden_compra(id_orden_compra),
    id_pedido           uuid REFERENCES pedido(id_pedido),
    monto_ahorrado      numeric(12, 2) NOT NULL,
    fecha_uso           timestamptz DEFAULT now()
);

CREATE TABLE historial_pedido (
    id_historial        bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_pedido           uuid NOT NULL REFERENCES pedido(id_pedido),
    estado_anterior     estado_pedido,
    estado_nuevo        estado_pedido NOT NULL,
    fecha_cambio        timestamptz DEFAULT now(),
    observacion         text,
    id_usuario_operador uuid REFERENCES usuario(id_usuario)
);

CREATE TABLE billetera_usuario (
    id_metodo           uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    tipo                tipo_metodo_pago NOT NULL,
    proveedor           proveedor_pago NOT NULL,
    token_externo       text NOT NULL,
    marca_tarjeta       text,
    ultimos_4_digitos   char(4),
    fecha_expiracion    char(5),
    es_predeterminado   boolean DEFAULT false,
    fecha_registro      timestamptz DEFAULT now(),
    activo              boolean DEFAULT true
);

CREATE TABLE transaccion_pago (
    id_transaccion      uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_orden_compra     uuid NOT NULL REFERENCES orden_compra(id_orden_compra),
    tipo_metodo         tipo_metodo_pago NOT NULL,
    id_metodo_guardado  uuid REFERENCES billetera_usuario(id_metodo),
    monto_total         numeric(12, 2) NOT NULL,
    moneda              char(3) DEFAULT 'BOB',
    estado              estado_transaccion DEFAULT 'pendiente',
    fecha_intento       timestamptz DEFAULT now(),
    fecha_confirmacion  timestamptz,
    id_referencia_banco text,
    qr_imagen_url       text,
    metadata_banco      jsonb
);

CREATE TABLE resena_producto (
    id_resena_prod      uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario),
    id_producto         uuid NOT NULL REFERENCES producto(id_producto),
    id_detalle_pedido   bigint NOT NULL REFERENCES detalle_pedido(id_detalle),
    puntuacion          int NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    comentario          text,
    fecha_registro      timestamptz DEFAULT now(),
    UNIQUE(id_usuario, id_detalle_pedido)
);

CREATE TABLE resena_sucursal (
    id_resena_suc       uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario),
    id_sucursal         uuid NOT NULL REFERENCES sucursal(id_sucursal),
    id_pedido           uuid NOT NULL REFERENCES pedido(id_pedido),
    puntuacion          int NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    aspectos_positivos  text[],
    comentario          text,
    fecha_registro      timestamptz DEFAULT now(),
    UNIQUE(id_usuario, id_pedido)
);

CREATE TABLE log_interaccion_ia (
    id_log              uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          uuid REFERENCES usuario(id_usuario),
    id_pedido           uuid REFERENCES pedido(id_pedido),
    fecha_hora          timestamptz DEFAULT now(),
    transcripcion_user  text,
    respuesta_ia        text,
    intencion           text,
    metadata_tecnica    jsonb
);

CREATE TABLE historial_vista (
    id_vista            bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    id_producto         uuid NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    ubicacion_viewer    point,
    fecha_vista         timestamptz DEFAULT now()
);

CREATE TABLE lista_deseos (
    id_deseo            uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          uuid NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    id_producto         uuid NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    fecha_agregado      timestamptz DEFAULT now(),
    UNIQUE (id_usuario, id_producto)
);

CREATE TABLE coleccion (
    id_coleccion        uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    titulo              text NOT NULL,
    descripcion         text,
    imagen_cover_url    text,
    id_usuario_destino  uuid REFERENCES usuario(id_usuario),
    es_generada_por_ia  boolean DEFAULT false,
    fecha_expiracion    timestamptz,
    activo              boolean DEFAULT true,
    fecha_creacion      timestamptz DEFAULT now()
);

CREATE TABLE item_coleccion (
    id_item_col         bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_coleccion        uuid NOT NULL REFERENCES coleccion(id_coleccion) ON DELETE CASCADE,
    id_producto         uuid NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    orden_visual        int DEFAULT 0,
    score_relevancia    numeric(5, 4)
);

CREATE INDEX idx_producto_vector ON producto USING GIN (vector_busqueda);
CREATE INDEX idx_sucursal_geo ON sucursal USING GIST (ubicacion_gps);
CREATE INDEX idx_usuario_telefono ON usuario(telefono);
CREATE INDEX idx_pedido_numero_publico ON pedido(numero_orden_publico);
CREATE INDEX idx_pedido_orden_padre ON pedido(id_orden_compra);
CREATE INDEX idx_pedido_sucursal_estado ON pedido (id_sucursal, fecha_creacion) WHERE estado = 'pendiente';
CREATE INDEX idx_inventario_sucursal ON inventario(id_sucursal);
CREATE INDEX idx_horario_sucursal ON horario(id_sucursal);
CREATE INDEX idx_config_entrega_sucursal ON config_entrega(id_sucursal) WHERE activo = true;
CREATE INDEX idx_colaborador_usuario ON colaborador(id_usuario);
CREATE INDEX idx_item_carrito_sucursal ON item_carrito(id_sucursal_origen);
CREATE INDEX idx_item_guardado_usuario ON item_guardado(id_usuario);
CREATE INDEX idx_billetera_usuario ON billetera_usuario(id_usuario) WHERE activo = true;
CREATE INDEX idx_historial_usuario_fecha ON historial_vista(id_usuario, fecha_vista DESC);
CREATE INDEX idx_coleccion_usuario ON coleccion(id_usuario_destino) WHERE activo = true;
CREATE INDEX idx_transaccion_orden ON transaccion_pago(id_orden_compra);
CREATE INDEX idx_promocion_activa ON promocion(activo, fecha_inicio, fecha_fin);
CREATE INDEX idx_lista_deseos_usuario ON lista_deseos(id_usuario);
CREATE INDEX idx_direccion_uso ON direccion_usuario(id_usuario, ultima_fecha_uso DESC);
CREATE INDEX idx_galeria_sucursal ON galeria_sucursal(id_sucursal);
CREATE INDEX idx_galeria_producto ON galeria_producto(id_producto);

INSERT INTO rol (nombre, descripcion) VALUES
('usuario',  'Rol base por defecto al registrarse'),
('dueño',    'Rol asignado al crear un negocio. Permite gestionar sucursales'),
('empleado', 'Rol asignado al ser contratado. Permite operar pedidos');
