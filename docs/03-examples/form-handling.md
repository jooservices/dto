# 📝 Form Handling Examples

Real-world examples of using DTOs for form processing in **jooservices/dto**.

---

## Table of Contents
1. [Simple Contact Form](#simple-contact-form)
2. [User Registration](#user-registration)
3. [Multi-Step Wizard](#multi-step-wizard)
4. [File Upload Form](#file-upload-form)
5. [Dynamic Form Fields](#dynamic-form-fields)
6. [Form Validation](#form-validation)

---

## Simple Contact Form

### Contact Form Data

```php
use JOOservices\Dto\Core\Data;
use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Attributes\Validation\Email;

class ContactFormData extends Data
{
    public function __construct(
        #[Required]
        public string $name = '',
        
        #[Required, Email]
        public string $email = '',
        
        public string $subject = '',
        
        #[Required]
        public string $message = '',
    ) {}
}
```

### Controller

```php
class ContactController
{
    public function submit(Request $request)
    {
        // Create form data with validation
        try {
            $form = ContactFormData::from(
                $request->all(),
                new Context(validationEnabled: true)
            );
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->getErrors())
                ->withInput();
        }
        
        // Process form
        $this->sendContactEmail($form);
        
        return redirect()
            ->route('contact.success')
            ->with('message', 'Thank you for contacting us!');
    }
    
    private function sendContactEmail(ContactFormData $form): void
    {
        Mail::to('support@example.com')
            ->send(new ContactEmail($form));
    }
}
```

### Email Class

```php
use Illuminate\Mail\Mailable;

class ContactEmail extends Mailable
{
    public function __construct(
        private ContactFormData $form
    ) {}
    
    public function build()
    {
        return $this->subject($this->form->subject ?: 'New Contact Form Submission')
            ->view('emails.contact', [
                'name' => $this->form->name,
                'email' => $this->form->email,
                'message' => $this->form->message,
            ]);
    }
}
```

---

## User Registration

### Registration Data

```php
use JOOservices\Dto\Core\Data;
use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Attributes\Validation\Email;

class RegistrationData extends Data
{
    public function __construct(
        #[Required]
        public string $username = '',
        
        #[Required, Email]
        public string $email = '',
        
        #[Required]
        public string $password = '',
        
        #[Required]
        public string $passwordConfirmation = '',
        
        public bool $agreedToTerms = false,
        
        public ?DateTimeImmutable $birthDate = null,
    ) {}
    
    public function validate(): array
    {
        $errors = [];
        
        // Password strength
        if (strlen($this->password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        
        if (!preg_match('/[A-Z]/', $this->password)) {
            $errors['password'] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $this->password)) {
            $errors['password'] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $this->password)) {
            $errors['password'] = 'Password must contain at least one number';
        }
        
        // Password confirmation
        if ($this->password !== $this->passwordConfirmation) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }
        
        // Terms agreement
        if (!$this->agreedToTerms) {
            $errors['agreed_to_terms'] = 'You must agree to the terms and conditions';
        }
        
        // Age verification (must be 13+)
        if ($this->birthDate !== null) {
            $age = $this->birthDate->diff(new DateTimeImmutable())->y;
            if ($age < 13) {
                $errors['birth_date'] = 'You must be at least 13 years old';
            }
        }
        
        return $errors;
    }
    
    public function toUserArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'birth_date' => $this->birthDate?->format('Y-m-d'),
        ];
    }
}
```

### Controller

```php
class RegistrationController
{
    public function register(Request $request)
    {
        // Create and validate form data
        try {
            $form = RegistrationData::from(
                $request->all(),
                new Context(validationEnabled: true)
            );
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->getErrors())
                ->withInput($request->except('password', 'password_confirmation'));
        }
        
        // Additional validation
        if ($errors = $form->validate()) {
            return back()
                ->withErrors($errors)
                ->withInput($request->except('password', 'password_confirmation'));
        }
        
        // Create user
        $user = User::create($form->toUserArray());
        
        // Send welcome email
        Mail::to($user->email)->send(new WelcomeEmail($user));
        
        // Log in user
        Auth::login($user);
        
        return redirect()
            ->route('dashboard')
            ->with('message', 'Welcome to our platform!');
    }
}
```

---

## Multi-Step Wizard

### Wizard Data

```php
class AccountWizardData extends Data
{
    public function __construct(
        // Step tracking
        public int $currentStep = 1,
        
        // Step 1: Basic Info
        public string $firstName = '',
        public string $lastName = '',
        public string $email = '',
        
        // Step 2: Account Details
        public string $username = '',
        public string $password = '',
        
        // Step 3: Preferences
        public string $timezone = 'UTC',
        public string $language = 'en',
        public bool $newsletter = false,
    ) {}
    
    public function validateStep(int $step): array
    {
        return match ($step) {
            1 => $this->validateStep1(),
            2 => $this->validateStep2(),
            3 => $this->validateStep3(),
            default => [],
        };
    }
    
    private function validateStep1(): array
    {
        $errors = [];
        
        if (empty($this->firstName)) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($this->lastName)) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }
        
        return $errors;
    }
    
    private function validateStep2(): array
    {
        $errors = [];
        
        if (strlen($this->username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        }
        
        if (strlen($this->password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        
        return $errors;
    }
    
    private function validateStep3(): array
    {
        // Preferences are optional
        return [];
    }
    
    public function nextStep(): void
    {
        $this->currentStep++;
    }
    
    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }
    
    public function isComplete(): bool
    {
        return $this->currentStep > 3;
    }
}
```

### Controller

```php
class WizardController
{
    public function show(Request $request)
    {
        // Get wizard data from session or create new
        $wizard = $request->session()->has('wizard')
            ? AccountWizardData::from($request->session()->get('wizard'))
            : new AccountWizardData();
        
        return view('wizard.step' . $wizard->currentStep, [
            'wizard' => $wizard,
        ]);
    }
    
    public function processStep(Request $request)
    {
        // Load wizard data from session
        $wizard = AccountWizardData::from(
            $request->session()->get('wizard', [])
        );
        
        // Update with form data
        $wizard->update($request->all());
        
        // Validate current step
        if ($errors = $wizard->validateStep($wizard->currentStep)) {
            return back()
                ->withErrors($errors)
                ->withInput();
        }
        
        // Move to next step
        $wizard->nextStep();
        
        // Save to session
        $request->session()->put('wizard', $wizard->toArray());
        
        // If complete, create account
        if ($wizard->isComplete()) {
            $this->createAccount($wizard);
            $request->session()->forget('wizard');
            
            return redirect()
                ->route('wizard.complete')
                ->with('message', 'Account created successfully!');
        }
        
        // Otherwise, show next step
        return redirect()->route('wizard.show');
    }
    
    public function previousStep(Request $request)
    {
        $wizard = AccountWizardData::from(
            $request->session()->get('wizard', [])
        );
        
        $wizard->previousStep();
        
        $request->session()->put('wizard', $wizard->toArray());
        
        return redirect()->route('wizard.show');
    }
    
    private function createAccount(AccountWizardData $wizard): User
    {
        return User::create([
            'first_name' => $wizard->firstName,
            'last_name' => $wizard->lastName,
            'email' => $wizard->email,
            'username' => $wizard->username,
            'password' => bcrypt($wizard->password),
            'timezone' => $wizard->timezone,
            'language' => $wizard->language,
            'newsletter' => $wizard->newsletter,
        ]);
    }
}
```

---

## File Upload Form

### Upload Form Data

```php
use JOOservices\Dto\Core\Data;
use Illuminate\Http\UploadedFile;

class FileUploadData extends Data
{
    public function __construct(
        public string $title = '',
        public string $description = '',
        public ?UploadedFile $file = null,
        public string $category = '',
        /** @var string[] */
        public array $tags = [],
    ) {}
    
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->title)) {
            $errors['title'] = 'Title is required';
        }
        
        if ($this->file === null) {
            $errors['file'] = 'File is required';
        } elseif (!$this->file->isValid()) {
            $errors['file'] = 'File upload failed';
        } elseif ($this->file->getSize() > 10 * 1024 * 1024) {
            $errors['file'] = 'File must be smaller than 10MB';
        } elseif (!in_array($this->file->extension(), ['pdf', 'doc', 'docx', 'jpg', 'png'])) {
            $errors['file'] = 'File type not allowed';
        }
        
        return $errors;
    }
}
```

### Controller

```php
class UploadController
{
    public function upload(Request $request)
    {
        $form = FileUploadData::from([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'file' => $request->file('file'),
            'category' => $request->input('category'),
            'tags' => $request->input('tags', []),
        ]);
        
        if ($errors = $form->validate()) {
            return back()
                ->withErrors($errors)
                ->withInput();
        }
        
        // Store file
        $path = $form->file->store('uploads', 'public');
        
        // Create database record
        $document = Document::create([
            'title' => $form->title,
            'description' => $form->description,
            'file_path' => $path,
            'file_name' => $form->file->getClientOriginalName(),
            'file_size' => $form->file->getSize(),
            'category' => $form->category,
            'tags' => $form->tags,
        ]);
        
        return redirect()
            ->route('documents.show', $document)
            ->with('message', 'File uploaded successfully!');
    }
}
```

---

## Dynamic Form Fields

### Dynamic Product Form

```php
class ProductFormData extends Data
{
    public function __construct(
        public string $name = '',
        public string $sku = '',
        public float $price = 0.0,
        public string $category = '',
        /** @var array<string, mixed> Custom fields */
        public array $attributes = [],
    ) {}
    
    public function addAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }
    
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
```

### Controller

```php
class ProductController
{
    public function store(Request $request)
    {
        $form = ProductFormData::from([
            'name' => $request->input('name'),
            'sku' => $request->input('sku'),
            'price' => $request->input('price'),
            'category' => $request->input('category'),
        ]);
        
        // Add dynamic attributes based on category
        if ($request->input('category') === 'electronics') {
            $form->addAttribute('warranty_months', $request->input('warranty_months'));
            $form->addAttribute('voltage', $request->input('voltage'));
        } elseif ($request->input('category') === 'clothing') {
            $form->addAttribute('size', $request->input('size'));
            $form->addAttribute('color', $request->input('color'));
            $form->addAttribute('material', $request->input('material'));
        }
        
        // Create product
        $product = Product::create([
            'name' => $form->name,
            'sku' => $form->sku,
            'price' => $form->price,
            'category' => $form->category,
            'attributes' => $form->attributes,
        ]);
        
        return redirect()
            ->route('products.show', $product)
            ->with('message', 'Product created successfully!');
    }
}
```

---

## Form Validation

### Custom Validation

```php
class LoginFormData extends Data
{
    public function __construct(
        public string $email = '',
        public string $password = '',
        public bool $remember = false,
    ) {}
    
    public function validate(): array
    {
        $errors = [];
        
        // Email validation
        if (empty($this->email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Password validation
        if (empty($this->password)) {
            $errors['password'] = 'Password is required';
        }
        
        // Check credentials
        if (empty($errors) && !Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
        ])) {
            $errors['email'] = 'Invalid credentials';
        }
        
        return $errors;
    }
}
```

### Reusable Validation Trait

```php
trait ValidatesForm
{
    public function validateAndReturn(array $data): static
    {
        $form = static::from($data, new Context(validationEnabled: true));
        
        if ($errors = $form->validate()) {
            throw ValidationException::withMessages($errors);
        }
        
        return $form;
    }
}

class CheckoutFormData extends Data
{
    use ValidatesForm;
    
    public function __construct(
        public string $name = '',
        public string $email = '',
        public AddressData $billingAddress = new AddressData(),
        public AddressData $shippingAddress = new AddressData(),
    ) {}
    
    public function validate(): array
    {
        // Custom validation logic
        return [];
    }
}

// Usage:
try {
    $form = CheckoutFormData::validateAndReturn($request->all());
    $this->processCheckout($form);
} catch (ValidationException $e) {
    return back()->withErrors($e->errors());
}
```

---

## Summary

- ✅ Use **Data** class for mutable form objects
- ✅ Combine **built-in validation** with **custom validation methods**
- ✅ Handle **multi-step forms** with step tracking
- ✅ Process **file uploads** with validation
- ✅ Support **dynamic fields** with arrays
- ✅ Create **reusable validation** patterns

---

**Next:** [Database Mapping](./database-mapping.md) | [Real-World Scenarios](./real-world-scenarios.md)
