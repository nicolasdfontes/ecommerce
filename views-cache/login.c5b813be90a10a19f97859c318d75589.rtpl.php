<?php if(!class_exists('Rain\Tpl')){exit;}?> 
<div class="product-big-title-area">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="product-bit-title text-center">
                    <h2>Autenticação</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="single-product-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">                
            <div class="col-md-6">
                <?php if( $error!='' ){ ?><div class="alert alert-danger"><?php echo htmlspecialchars( $error, ENT_COMPAT, 'UTF-8', FALSE ); ?></div><?php } ?>

                <form action="/login" id="login-form-wrap" class="login" method="post">
                    <h2>Já tenho conta</h2>
                    <p class="form-row form-row-first">
                        <label for="login">E-mail <span class="required">*</span></label>
                        <input type="text" id="login" name="login" class="input-text">
                    </p>
                    <p class="form-row form-row-last">
                        <label for="senha">Senha <span class="required">*</span></label>
                        <input type="password" id="senha" name="senha" class="input-text">
                    </p>
                    <div class="clear"></div>
                    <p class="form-row">
                        <input type="submit" value="Login" class="button">
                        <label class="inline" for="remember"><input type="checkbox" value="forever" id="remember" name="remember"> Manter conectado</label>
                    </p>
                    <p class="lost_password"><a href="/forgot">Esqueceu a senha?</a></p>
                    <div class="clear"></div>
                </form>                    
            </div>
            <div class="col-md-6">
                <?php if( $errorRegister!='' ){ ?><div class="alert alert-danger"><?php echo htmlspecialchars( $errorRegister, ENT_COMPAT, 'UTF-8', FALSE ); ?></div><?php } ?>

                <form action="/register" id="register-form-wrap" class="register" method="post">
                    <h2>Criar conta</h2>
                    <p class="form-row form-row-first">
                        <label for="nome">Nome Completo <span class="required">*</span></label>
                        <input type="text" id="nome" name="name" value="<?php echo htmlspecialchars( $regVal["name"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" class="input-text">
                    </p>
                    <p class="form-row form-row-first">
                        <label for="email">E-mail <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars( $regVal["email"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" class="input-text">
                    </p>
                    <p class="form-row form-row-first">
                        <label for="phone">Telefone</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars( $regVal["phone"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" class="input-text" placeholder="(__)________" maxlength=11>
                    </p>
                    <p class="form-row form-row-last">
                        <label for="senha">Senha <span class="required">*</span></label>
                        <input type="password" id="senha" name="senha" class="input-text" minlength=6>
                    </p>
                    <div class="clear"></div>
                    <p class="form-row"><input type="submit" value="Criar" name="login" class="button"></p>
                    <div class="clear"></div>
                </form>               
            </div>
        </div>
    </div>
</div>